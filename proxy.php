<?php
// 获取原始 URL 参数
$url = $_SERVER['REQUEST_URI'];

// 检查 URL 是否以 /file/ 开头
if (strpos($url, '/file/') === 0) {
  // 构造图片 URL
  $image_url = 'https://telegra.ph' . $url;

  // 发送代理请求并输出响应内容
  $options = array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true, // 返回headers
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING       => '',
    CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
    CURLOPT_AUTOREFERER    => true,
    CURLOPT_CONNECTTIMEOUT => 120,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false
  );

  $ch = curl_init($image_url);
  curl_setopt_array($ch, $options);
  $response = curl_exec($ch);
  $err = curl_errno($ch);
  $errmsg = curl_error($ch);
  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); // 获取headers大小
  $header = substr($response, 0, $header_size); // 提取headers
  $content = substr($response, $header_size); // 提取响应内容
  curl_close($ch);

  if ($errmsg != '') {
    echo "Error: $errmsg";
  } else {
    // 检查content-type是否在headers中存在
    if (preg_match('/Content-Type:\s*(\S+)/i', $header, $matches)) {
      $content_type = $matches[1];
    } else {
      // 若不存在，从URL中获取文件扩展名
      $extension = pathinfo($url, PATHINFO_EXTENSION);
      // 映射扩展名到content-type
      switch ($extension) {
        case 'jpg':
        case 'jpeg':
          $content_type = 'image/jpeg';
          break;
        case 'png':
          $content_type = 'image/png';
          break;
        case 'gif':
          $content_type = 'image/gif';
          break;
        default:
          // 若不在映射范围中，返回404
          http_response_code(404);
          die('File not found');
      }
    }
    header('Content-Type: ' . $content_type);
    echo $content;
  }
} else {
  // 直接返回空响应
  header('Content-Type: text/plain');
  echo '';
}
?>
