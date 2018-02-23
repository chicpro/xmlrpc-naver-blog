# Naver blog post using XMLRPC #

XMLRPC 프로토콜을 이용해 네이버 블로그에 게시글을 등록합니다.

#### 참고자료 ####

- [XML- RPC](http://xmlrpc.scripting.com/)
- [XMLRPC for PHP](http://gggeek.github.io/phpxmlrpc/)
- [MetaWebblog API](https://msdn.microsoft.com/ko-kr/library/bb259697.aspx)

## 설치 ##

아래의 명령을 실행해 설치합니다. [XMLRPC for PHP](https://github.com/gggeek/phpxmlrpc/) 클라이언트 설치가 필요하기 때문에 composer를 이용합니다.

`git clone https://github.com/chicpro/xmlrpc-naver-blog.git`

`composer update`

## 예제 ##

```
<?php
namespace chicpro;

//ini_set('display_errors', 1);

require 'vendor/autoload.php';

use chicpro\NaverBlog\NaverBlog;

$naverID = '네이버아이디';
$naverPW = '네이버블로그 API연결 암호';

$blog = new NaverBlog();

$blog->setCredentials($naverID, $naverPW);

$file = './files/test-image.jpg';

$media = $blog->uploadMedia($file);

$title    = '블로그 포스트 제목';
$content  = '블로그 내용<br>테스트입니다.';
$category = '테스트분류';
$tags     = '태그1,태그2';

if(isset($media['url']) && $media['url'])
    $content .= '<br><br><img src="'.$media['url'].'">';

$post = $blog->newPost($title, $content, $category, $tags);

if(isset($post['post']) && $post['post'])
    echo 'Post ID : '.$post['post'];
else
    echo 'Posting failed.';
```

`$naverID`와 `$naverPW` 값을 올바르게 설정한 후 코드를 실행합니다.

아이디와 암호는 네이버블로그 관리 페이지에서 확인할 수 있습니다.

위 코드는 이미지 업로드 후 업로드 이미지의 url 정보를 얻어 포스트 내용에 img 태그를 추가한 후 게시글을 등록합니다.