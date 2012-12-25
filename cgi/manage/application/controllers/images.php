<?php
class Images extends CI_Controller {
  
  public $upaiyun_domain = "http://regionpoipic.b0.upaiyun.com/";
  
  public $googleImages_dir = "/sdb2/googleImages/";
  
  public $trash_foler = "/sdb2/googleImages/Trash/";
  
  public $ps = 20;
  
  public function index() {
    $this->load->view('images_index');
  }
  
  public function region() {
    $this->show_list('Region');
  }
  
  public function poi() {
    $this->show_list('POI');
  }
  
  private function show_list($collection) {
    $this->collection = $collection;
    
    
    $this->title = "$collection Images manage";
    $pg = $this->input->get('pg');
    ($pg <= 0 || empty($pg)) && $pg = 1;
    $this->q = $this->input->get('q');
    $query = array();
    $func_nav = $collection.'_distinct_by_category';
	$cate_arr = $this->mongo_db->$func_nav();
	$cate_arr = array_filter($cate_arr);
	sort($cate_arr);
	$this->nav = array_combine($cate_arr, array_fill(0, count($cate_arr), false));
	$category = $this->input->get('category');
	if (!empty($category)) {
	  $query['category'] = $category;
	  
	}
	foreach ($this->nav AS $cate => $selected) {
	  if ($cate != $category) continue;
	  $this->nav[$cate] = true; break;
	}
	
    !empty($this->q) && $query['name'] = array('$regex' => $this->q);
    if ($this->collection == 'POI') {
      $regionId = $this->input->get('regionId');
      !empty($regionId) && $query['regionId'] = new MongoId($regionId);
    }
    $iterator = $this->mongo_db->con->tripfm->$collection->find($query)->sort(array('rank' => -1));
    //if (!empty($regionId)) {
    //  $iterator = $iterator->sort(array('rank' => -1));
    //} else {
    //  $iterator = $iterator->sort(array('name' => -1));
    //}
    
    $this->count = $iterator->count();
    $iterator =  $iterator->skip(($pg - 1) * $this->ps)->limit($this->ps);
    $res = array_values(iterator_to_array($iterator));
    foreach ($res AS &$poi) {
      foreach($poi['googleImages'] AS &$image) {
        $url = $this->get_image($collection, $image['imageId'], '');
        if (!$url) continue;
        $image['up_url'] = $url;
      }
      unset($image);
    }
    unset($poi);
    $param = array(
      'list' => $res,
    );
    $this->load->view('images_list', $param);
  }
  
  public function move_to_trash($type, $mongoId, $picid) {
    $collection = (strtolower($type) == 'poi') ? 'POI' : 'Region';
    $res = array(
             'response_code' => '200',
             'response_msg'  => '',
             'response_data' => '',
    );
    $tripfm = $this->mongo_db->con->tripfm;
    $data = $tripfm->$collection->findOne(
      array('_id' => new MongoId($mongoId)), array('googleImages' => true));
    
    $googleImages = $data['googleImages'];
    foreach ($googleImages AS $pic_pos => $pic) {
      if ($pic['imageId'] == $picid) break;
    }
    if(!$tripfm->$collection->update(
      array('_id' => new MongoId($mongoId)), 
      array('$unset' => array("googleImages.$pic_pos" => 1)))) {
      $res['response_msg'] = 'update db error'; goto response;
    }
    if(!$tripfm->$collection->update(
      array('_id' => new MongoId($mongoId)), 
      array('$pull' => array("googleImages" => null)))) {
      $res['response_msg'] = 'update db error'; goto response;
    }
    !is_dir($this->trash_foler.$collection) && 
      mkdir($this->trash_foler.$collection, 0755, true);
    if (!rename($this->googleImages_dir.$collection.'/'.$picid, 
                $this->trash_foler.$collection.'/'.$picid)) {
       $res['response_msg'] = 'move to trash error'; goto response;
    }
    $res['response_code'] = 100;
  response:
    die(json_encode($res));
  }
  
  public function upload($type, $mongoId, $picid = null) {
    $collection = (strtolower($type) == 'poi') ? 'POI' : 'Region';
    $tripfm = $this->mongo_db->con->tripfm;
    $data = $tripfm->$collection->findOne(
      array('_id' => new MongoId($mongoId)), array('googleImages' => true));
    $googleImages = $data['googleImages'];
    $param = array('collection' => $collection, 'mongoId' => $mongoId, 'code' => 0);
    if(empty($_FILES)) goto view;
    $file = $_FILES['upload-img'];
	if($file['error'] != UPLOAD_ERR_OK) {
		$param['code'] = 200;
		goto view;
	} //imageId
	$pic_item = null;
	$pic_pos = -1;
	foreach ($googleImages AS $pic_no => $pic) {
	  if ($pic['imageId'] != $picid) continue; 
	  $pic_pos = $pic_no;
	  $pic_item = $pic;
	}
	$pic_pos == -1 && $pic_pos = count($googleImages);
	if (empty($picid)) $picid = md5_file($file['tmp_name']);
	$param['picid'] = $picid;
	$pic_item['imageId'] = $picid;
	$stat = getimagesize($file['tmp_name']);
    if ($stat === false) {
      $param['code'] = 200;
	  goto view;
    }
    list(, $imageType) = explode('/', $stat['mime']);
    $pic_item['imageType'] = $imageType;
	$filename = $this->googleImages_dir.$collection."/".$picid;
    move_uploaded_file($file['tmp_name'], $filename);
    if(!$this->uploadToUpaiyun($filename, $collection."/".$picid)) goto view;
    $tripfm->$collection->update(
      array('_id' => new MongoId($mongoId)), array('$push' => array("googleImages" => $pic_item)));
	$param['code'] = 100;
	$param['url'] = $this->get_image($collection, $picid, '');
  view:
    $this->load->view('images_upload', $param);
  }
  
  private function get_image($type, $id, $size) { 
    $collection = (strtolower($type) == 'poi') ? 'POI' : 'Region';
    $pic_filename = $this->googleImages_dir.$collection."/".$id;
    if (!is_file($pic_filename)) return false;
    $image_stat = getimagesize($pic_filename);
    list(, $img_type) = explode('/', $image_stat['mime']);
    return $this->upaiyun_domain.$collection."/{$id}.{$img_type}!{$size}";
  }

  
  private function uploadToUpaiyun($filename, $url_path) {
    $image_stat = getimagesize($filename);
    list(, $img_type) = explode('/', $image_stat['mime']);
    $ch = curl_init("http://v0.api.upyun.com/regionpoipic/$url_path.$img_type");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filename));
    curl_setopt($ch, CURLOPT_USERPWD, "cheewuftp:iamgo2010");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    /// 如果需要自动创建父级目录功能（仅限10 级）
    /// curl_setopt($process, CURLOPT_HTTPHEADER, array('Expect:', "Mkdir:true"));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1); /// 获取 header （如需获取图片上传成功后的信息)
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code == 200;
  }
  

  
}