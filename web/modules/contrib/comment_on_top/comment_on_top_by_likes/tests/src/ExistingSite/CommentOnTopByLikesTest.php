<?php

  namespace Drupal\Tests\comment_on_top_by_likes\ExistingSite;

  use Drupal\comment\Entity\Comment;
  use Drupal\devel\Plugin\Devel\Dumper\Kint;
  use weitzman\DrupalTestTraits\ExistingSiteBase;
  use Drupal\Core\Database\Connection;

  class CommentOnTopByLikesTest extends ExistingSiteBase {

    protected $commentOnTopByLikesPerNode;

    public function testCommentOnTopByLikes() {

      //Create user
      $user = $this->createUser([], null, true);
      $this->drupalLogin($user);

      //Create article with checked Stick on top by likes checkbox
      $this->drupalGet('/node/add/article');
      $page = $this->getSession()->getPage();
      $page->fillField('title[0][value]', 'Test article');
      $page->fillField('body[0][value]', 'Test article body Test article body Test article body ');

      $submit_button = $page->findButton('Save');
      $submit_button->press();

      $current_url = $this->getSession()->getCurrentUrl();

      $url_segments = explode('/', $current_url);
      $nid = intval(end($url_segments));

      //Test against db table comment__stick_on_top_by_likes column sticked_on_top_by_likes is equal 0
      $commentOnTopService = \Drupal::service('comment_on_top_by_likes.node');
      $stickOnTopByLikesValue = $commentOnTopService->getStickedOnTopByLikes($nid);
      $this->assertEquals(0, $stickOnTopByLikesValue, 'Stick on top by likes is CHECKED');

      //Add first comment
      //Creating comment 1 for node with id 9999991. With 1 like
      $comment1 = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => $nid,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 1',
        'comment_body' => 'Test comment 1 Test comment 1 Test comment 1 ',
        'cid' => 9999991,
        'uid' => 1,
        'field_like_dislike' => 2,
        'status' => 1,
      ]);
      $comment1->save();

      //Add first comment reply one
      //Creating reply for comment 1 with id 99999911. With 3 likes
      $comment1reply = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => $nid,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 1 reply',
        'comment_body' => 'Test comment 1 reply Test comment 1 reply Test comment 1 reply ',
        'cid' => 99999911,
        'pid' => 9999991,
        'uid' => 1,
        'field_like_dislike' => 3,
        'status' => 1,
      ]);
      $comment1reply->save();

      //Add first comment reply on reply one
      //Creating reply for reply comment 1 with id 999999111. With 4 likes
      $comment1replyOnReply = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => $nid,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 1 reply on reply',
        'comment_body' => 'Test comment 1 reply on reply Test comment 1 reply on reply ',
        'cid' => 999999111,
        'pid' => 9999991,
        'uid' => 1,
        'field_like_dislike' => 4,
        'status' => 1,
      ]);
      $comment1replyOnReply->save();

      //Add second comment
      //Creating comment 2 for node with id 9999992. With 5 likes
      $comment2 = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => $nid,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 2',
        'comment_body' => 'Test comment 2 Test comment 2 Test comment 2 ',
        'cid' => 9999992,
        'uid' => 1,
        'field_like_dislike' => 5,
        'status' => 1,
      ]);
      $comment2->save();

      //Add second comment reply one
      //Creating reply for comment 2 with id 99999921. With 1 like
      $comment2reply = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => $nid,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 2 reply',
        'comment_body' => 'Test comment 2 reply Test comment 2 reply Test comment 2 reply ',
        'cid' => 99999921,
        'pid' => 9999992,
        'uid' => 1,
        'field_like_dislike' => 1,
        'status' => 1,
      ]);
      $comment2reply->save();

      //Add second comment reply on reply one
      //Creating reply for reply comment 2 with id 999999211
      $comment2replyOnReply = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => $nid,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 2 reply on reply',
        'comment_body' => 'Test comment 2 reply on reply Test comment 2 reply on reply ',
        'cid' => 999999211,
        'pid' => 9999992,
        'uid' => 1,
        'status' => 1,
      ]);
      $comment2replyOnReply->save();

      //Add third comment
      //Creating comment 3 for node with id 9999993. With 1 like
      $comment3 = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => $nid,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 3',
        'comment_body' => 'Test comment 3 Test comment 3 Test comment 3 ',
        'cid' => 9999993,
        'uid' => 1,
        'field_like_dislike' => 1,
        'status' => 1,
      ]);
      $comment3->save();

      $this->drupalGet($current_url);

      $this->drupalGet('/node/'.$nid.'/edit');

      $page = $this->getSession()->getPage();
      $stickOnTopByLikes = $page->find('css', '#edit-comment-0-stick-on-top-by-likes');
      $stickOnTopByLikes->check();

      $submit_button = $page->findButton('Save');
      $submit_button->press();

      //Test against db table comment__stick_on_top_by_likes column sticked_on_top_by_likes is equal 1
      $stickOnTopByLikesValue = $commentOnTopService->getStickedOnTopByLikes($nid);
      $this->assertEquals(1, $stickOnTopByLikesValue, 'Stick on top by likes is NOT CHECKED');

      $this->drupalGet($current_url);

      //Test comment order with its replies. Is second comment with its replies on top
      $pageContent = $page->getContent();

      $comment1Position = strpos($pageContent, '<h3><a href="/comment/9999991#comment-9999991" class="permalink" rel="bookmark" hreflang="en">Test comment 1</a></h3>');
      $comment1ReplyPosition = strpos($pageContent, 'href="/comment/99999911#comment-99999911"');
      $comment1ReplyOnReplyPosition = strpos($pageContent, 'href="/comment/999999111#comment-999999111"');
      $comment2Position = strpos($pageContent, '<h3><a href="/comment/9999992#comment-9999992" class="permalink" rel="bookmark" hreflang="en">Test comment 2</a></h3>');
      $comment2ReplyPosition = strpos($pageContent, 'href="/comment/99999921#comment-99999921"');
      $comment2ReplyOnReplyPosition = strpos($pageContent, 'href="/comment/999999211#comment-999999211"');
      $comment3Position = strpos($pageContent, 'href="/comment/9999993#comment-9999993"');

      //Testing is sticked comment 2 to top along with its first reply
      $this->assertLessThan(
        $comment2ReplyPosition,
        $comment2Position, 'Comment 2 is not above Comment 2 reply!'
      );

      //Testing is comment 2 reply above comment 2 reply of reply
      $this->assertLessThan(
        $comment2ReplyOnReplyPosition,
        $comment2ReplyPosition, 'Comment 2 reply is not above Comment 2 reply on reply!'
      );

      //Testing is comment 2 reply on reply above comment 1
      $this->assertLessThan(
        $comment1Position,
        $comment2ReplyOnReplyPosition, 'Comment 2 reply on reply is not above Comment 1!'
      );

      //Testing is comment 1 above comment 1 reply
      $this->assertLessThan(
        $comment1ReplyPosition,
        $comment1Position, 'Comment 1 is not above Comment 1 reply!'
      );

      //Testing is comment 1 reply above comment 1 reply on reply
      $this->assertLessThan(
        $comment1ReplyOnReplyPosition,
        $comment1ReplyPosition, 'Comment 1 reply is not above Comment 1 reply on reply!'
      );

      //Testing is comment 1 reply on reply above comment 3
      $this->assertLessThan(
        $comment3Position,
        $comment1ReplyOnReplyPosition, 'Comment 1 reply on reply is not above Comment 3!'
      );

      //Edit article and uncheck Stick on top by likes and test is comment order by Drupal default
      $this->drupalGet('/node/'.$nid.'/edit');

      $page = $this->getSession()->getPage();
      $stickOnTopByLikes = $page->find('css', '#edit-comment-0-stick-on-top-by-likes');
      $stickOnTopByLikes->uncheck();

      $submit_button = $page->findButton('Save');
      $submit_button->press();

      $this->drupalGet($current_url);

      $pageContent = $page->getContent();

      $comment1Position = strpos($pageContent, '<h3><a href="/comment/9999991#comment-9999991" class="permalink" rel="bookmark" hreflang="en">Test comment 1</a></h3>');
      $comment1ReplyPosition = strpos($pageContent, 'href="/comment/99999911#comment-99999911"');
      $comment1ReplyOnReplyPosition = strpos($pageContent, 'href="/comment/999999111#comment-999999111"');
      $comment2Position = strpos($pageContent, '<h3><a href="/comment/9999992#comment-9999992" class="permalink" rel="bookmark" hreflang="en">Test comment 2</a></h3>');
      $comment2ReplyPosition = strpos($pageContent, 'href="/comment/99999921#comment-99999921"');
      $comment2ReplyOnReplyPosition = strpos($pageContent, 'href="/comment/999999211#comment-999999211"');
      $comment3Position = strpos($pageContent, 'href="/comment/9999993#comment-9999993"');

      //Testing is comment 1 above comment 1 reply
      $this->assertLessThan(
        $comment1ReplyPosition,
        $comment1Position, 'Comment 1 is not above Comment 1 reply!'
      );

      //Testing is comment 1 reply above comment 1 reply on reply
      $this->assertLessThan(
        $comment1ReplyOnReplyPosition,
        $comment1ReplyPosition, 'Comment 1 reply is not above Comment 1 reply on reply!'
      );

      //Testing is comment 1 reply on reply above comment 2
      $this->assertLessThan(
        $comment2Position,
        $comment1ReplyOnReplyPosition, 'Comment 1 reply on reply i not above Comment 2!'
      );

      //Testing is comment 2 above comment 2 reply
      $this->assertLessThan(
        $comment2ReplyPosition,
        $comment2Position, 'Comment 2 is not above Comment 2 reply!'
      );

      //Testing is comment 2 reply above comment 2 reply on reply
      $this->assertLessThan(
        $comment2ReplyOnReplyPosition,
        $comment2ReplyPosition, 'Comment 2 reply is not above Comment 2 reply on reply!'
      );

      //Testing is comment 2 reply on reply above comment 3
      $this->assertLessThan(
        $comment3Position,
        $comment2ReplyOnReplyPosition, 'Comment 2 reply on reply is not above comment 3!'
      );

    }
  }
