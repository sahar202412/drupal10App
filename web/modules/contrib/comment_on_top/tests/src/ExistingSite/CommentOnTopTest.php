<?php

  namespace Drupal\Tests\comment_on_top\ExistingSite;

  use Drupal\devel\Plugin\Devel\Dumper\Kint;
  use weitzman\DrupalTestTraits\ExistingSiteBase;
  use Drupal\comment_on_top\Controller\CommentOnTopController;
  use Drupal\comment\Entity\Comment;
  use Drupal\views\Views;

  class CommentOnTopTest extends ExistingSiteBase {

    protected function setUp(): void {
      parent::setUp();

      // Cause tests to fail if an error is sent to Drupal logs.
      $this->failOnLoggedErrors();
    }

    public function testCommentOnTop() {

      //Creating node 999999
      $node = $this->createNode([
        'title' => 'Test article',
        'body' => 'Test article Test article Test article Test article ',
        'type' => 'article',
        'uid' => 1,
        'nid' => 999999
      ]);
      $node->setPublished()->save();

      $this->drupalGet($node->toUrl());

      //Creating comment 1 for node with id 9999991
      $comment1 = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => 999999,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 1',
        'comment_body' => 'Test comment 1 Test comment 1 Test comment 1 ',
        'cid' => 9999991,
        'uid' => 1,
        'status' => 1,
      ]);
      $comment1->save();

      //Creating comment 2 for node with id 9999992
      $comment2 = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => 999999,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 2',
        'comment_body' => 'Test comment 2 Test comment 2 Test comment 2 ',
        'cid' => 9999992,
        'uid' => 1,
        'status' => 1,
      ]);
      $comment2->save();

      //Creating reply for comment 2 with id 9999992
      $comment2reply = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => 999999,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 2 reply',
        'comment_body' => 'Test comment 2 reply Test comment 2 reply Test comment 2 reply ',
        'cid' => 9999993,
        'pid' => 9999992,
        'uid' => 1,
        'status' => 1,
      ]);
      $comment2reply->save();

      //Creating third comment 3 for node with id 9999992
      $comment3 = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => 999999,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 3',
        'comment_body' => 'Test comment 3 Test comment 3 Test comment 3 ',
        'cid' => 9999994,
        'uid' => 1,
        'status' => 1,
      ]);
      $comment3->save();

      //Creating reply for comment 3 with id 9999992
      $comment3reply = Comment::create([
        'entity_type' => 'node',
        'entity_id'   => 999999,
        'field_name'  => 'comment',
        'comment_type' => 'comment',
        'subject' => 'Test comment 3 reply',
        'comment_body' => 'Test comment 3 reply Test comment 3 reply Test comment 3 reply ',
        'cid' => 9999995,
        'pid' => 9999994,
        'uid' => 1,
        'status' => 1,
      ]);
      $comment3reply->save();

      $this->drupalGet($node->toUrl());

      $user = $this->createUser([], null, true);
      $this->drupalLogin($user);

      //Sticking on top comment 3 with id 9999994 along with its reply (comment with id 9999995)
      $controllerContainer = \Drupal::getContainer();
      $controller = CommentOnTopController::create($controllerContainer);
      $controller->stickOnTop(999999, 9999994);
      $this->drupalGet($node->toUrl());

      $page = $this->getSession()->getPage();

      $pageContent = $page->getContent();

      $comment1Position = strpos($pageContent, 'href="/comment/9999991#comment-9999991"');
      $comment2Position = strpos($pageContent, 'href="/comment/9999992#comment-9999992"');
      $comment2ReplyPosition = strpos($pageContent, 'href="/comment/9999993#comment-9999993"');
      $comment3Position = strpos($pageContent, 'href="/comment/9999994#comment-9999994"');
      $comment3ReplyPosition = strpos($pageContent, 'href="/comment/9999995#comment-9999995"');

      //Testing is sticked comment 3 to top along with its reply
      $this->assertLessThan(
        $comment3ReplyPosition,
        $comment3Position, 'Comment 3 is not above Comment 3 reply!'
      );

      //Testing is comment 1 bellow sticked comment reply
      $this->assertLessThan(
        $comment1Position,
        $comment3ReplyPosition, 'Comment 3 reply is not above Comment 1!'
      );

      // Testing is comment 2 bellow comment 1
      $this->assertLessThan(
        $comment2Position,
        $comment1Position, 'Comment 1 is not above Comment 2!'
      );

      // Testing is comment 2 reply bellow comment 2
      $this->assertLessThan(
        $comment2ReplyPosition,
        $comment2Position, 'Comment 2 is not above Comment 2 reply!'
      );

      //Testing is comment with cid 9999994 has value sticked_on_top 1 and comment with cid 9999991 has null
      $view = Views::getView('comments_with_one_sticked_on_top');
      if (is_object($view)) {
        $view->setArguments([999999]);
        $view->setDisplay('bcsot');
        $view->preExecute();
        $view->execute();
        $content = $view->buildRenderable('bcsot', [999999]);
      }
      $comments = $content['#view']->result;

      $commentStickedOnTop0 = $comments[0]->comment__field_stick_comment_on_top_boole_field_stick_commen;
      $cid0 = $comments[0]->_entity->id();

      $commentStickedOnTop1 = $comments[1]->comment__field_stick_comment_on_top_boole_field_stick_commen;
      $cid1 = $comments[1]->_entity->id();

      //Testing is sticked comment has value 1 on field Sticked on top and its cid
      $this->assertEquals(9999994, $cid0, 'Sticked comment has not cid 9999994');
      $this->assertEquals(1, $commentStickedOnTop0, 'Sticked comment has not value 1 on field Sticked on top');

      //Testing is not sticked comment has value null on field Sticked on top and its cid
      $this->assertEquals(9999991, $cid1, 'Comment has not cid 9999991');
      $this->assertEquals(0, $commentStickedOnTop1, 'Comment has not value 0 on field Sticked on top');

      //Sticking on top comment 2 with id 9999992 along with its reply (comment with id 9999993)
      $controller->stickOnTop(999999, 9999992);
      $this->drupalGet($node->toUrl());

      $page = $this->getSession()->getPage();

      $pageContent = $page->getContent();

      $comment1Position = strpos($pageContent, 'href="/comment/9999991#comment-9999991"');
      $comment2Position = strpos($pageContent, 'href="/comment/9999992#comment-9999992"');
      $comment2ReplyPosition = strpos($pageContent, 'href="/comment/9999993#comment-9999993"');
      $comment3Position = strpos($pageContent, 'href="/comment/9999994#comment-9999994"');
      $comment3ReplyPosition = strpos($pageContent, 'href="/comment/9999995#comment-9999995"');

      // Testing is comment 2 reply bellow comment 2
      $this->assertLessThan(
        $comment2ReplyPosition,
        $comment2Position, 'Comment 2 is not above Comment 2 reply!'
      );

      // Testing is comment 1 bellow comment 2 reply
      $this->assertLessThan(
        $comment1Position,
        $comment2ReplyPosition, 'Comment 2 reply is not above Comment 1!'
      );

      // Testing is comment 3 bellow comment 1
      $this->assertLessThan(
        $comment3Position,
        $comment1Position, 'Comment 1 is not above Comment 2!'
      );

      // Testing is comment 3 reply bellow comment 3
      $this->assertLessThan(
        $comment3ReplyPosition,
        $comment3Position, 'Comment 3 is not above Comment 3 reply!'
      );

      //Testing does comment with cid 9999992 has value sticked_on_top 1 and comment with cid 9999991 has 0
      $view = Views::getView('comments_with_one_sticked_on_top');
      if (is_object($view)) {
        $view->setArguments([999999]);
        $view->setDisplay('bcsot');
        $view->preExecute();
        $view->execute();
        $content = $view->buildRenderable('bcsot', [999999]);
      }
      $comments = $content['#view']->result;

      $commentStickedOnTop0 = $comments[0]->comment__field_stick_comment_on_top_boole_field_stick_commen;
      $cid0 = $comments[0]->_entity->id();

      $commentStickedOnTop3 = $comments[1]->comment__field_stick_comment_on_top_boole_field_stick_commen;
      $cid3 = $comments[1]->_entity->id();

      //Does sticked comment has value 1 on field Sticked on top and its cid
      $this->assertEquals(9999992, $cid0, 'Sticked comment has not cid 9999992');
      $this->assertEquals(1, $commentStickedOnTop0, 'Sticked comment has not value 1 on field Sticked on top');

      //Testing is not sticked comment has value null on field Sticked on top and its cid
      $this->assertEquals(9999991, $cid3, 'Comment has not cid 9999991');
      $this->assertEquals(0, $commentStickedOnTop3, 'Comment has not value 0 on field Sticked on top');

      //Removing top comment with id 9999992 along with its reply (comment with id 9999993)
      $controller->removeFromTop(999999, 9999992);
      $this->drupalGet($node->toUrl());

      $page = $this->getSession()->getPage();

      $pageContent = $page->getContent();

      $comment1Position = strpos($pageContent, 'href="/comment/9999991#comment-9999991"');
      $comment2Position = strpos($pageContent, 'href="/comment/9999992#comment-9999992"');
      $comment2ReplyPosition = strpos($pageContent, 'href="/comment/9999993#comment-9999993"');
      $comment3Position = strpos($pageContent, 'href="/comment/9999994#comment-9999994"');
      $comment3ReplyPosition = strpos($pageContent, 'href="/comment/9999995#comment-9999995"');

      // Testing is comment 2 bellow comment 1
      $this->assertLessThan(
        $comment2Position,
        $comment1Position, 'Comment 1 is not above Comment 2!'
      );

      // Testing is comment 2 reply bellow comment 2
      $this->assertLessThan(
        $comment2ReplyPosition,
        $comment2Position, 'Comment 2 is not above Comment 2 reply!'
      );

      // Testing is comment 3 bellow comment 2 reply
      $this->assertLessThan(
        $comment3Position,
        $comment2ReplyPosition, 'Comment 2 reply is not above Comment 3!'
      );

      // Testing is comment 3 reply bellow comment 3
      $this->assertLessThan(
        $comment3ReplyPosition,
        $comment3Position, 'Comment 3 is not above Comment 3 reply!'
      );

      $this->markEntityForCleanUp($node);
    }
  }
