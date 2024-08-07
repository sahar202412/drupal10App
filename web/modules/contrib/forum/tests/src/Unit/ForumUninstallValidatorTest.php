<?php

declare(strict_types=1);

namespace Drupal\Tests\forum\Unit;

use Drupal\Core\Url;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\forum\ForumUninstallValidator
 * @group forum
 */
class ForumUninstallValidatorTest extends UnitTestCase {

  /**
   * @var \Drupal\forum\ForumUninstallValidator|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $forumUninstallValidator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->forumUninstallValidator = $this->getMockBuilder('Drupal\forum\ForumUninstallValidator')
      ->disableOriginalConstructor()
      ->onlyMethods(['hasForumNodes', 'hasTermsForVocabulary', 'getForumVocabulary'])
      ->getMock();
    $this->forumUninstallValidator->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * @covers ::validate
   */
  public function testValidateNotForum(): void {
    $this->forumUninstallValidator->expects($this->never())
      ->method('hasForumNodes');
    $this->forumUninstallValidator->expects($this->never())
      ->method('hasTermsForVocabulary');
    $this->forumUninstallValidator->expects($this->never())
      ->method('getForumVocabulary');

    $module = 'not_forum';
    $expected = [];
    $reasons = $this->forumUninstallValidator->validate($module);
    $this->assertEquals($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidate(): void {
    $this->forumUninstallValidator->expects($this->once())
      ->method('hasForumNodes')
      ->willReturn(FALSE);

    $vocabulary = $this->createMock('Drupal\taxonomy\VocabularyInterface');
    $this->forumUninstallValidator->expects($this->once())
      ->method('getForumVocabulary')
      ->willReturn($vocabulary);

    $this->forumUninstallValidator->expects($this->once())
      ->method('hasTermsForVocabulary')
      ->willReturn(FALSE);

    $module = 'forum';
    $expected = [];
    $reasons = $this->forumUninstallValidator->validate($module);
    $this->assertEquals($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidateHasForumNodes(): void {
    $this->forumUninstallValidator->expects($this->once())
      ->method('hasForumNodes')
      ->willReturn(TRUE);

    $vocabulary = $this->createMock('Drupal\taxonomy\VocabularyInterface');
    $this->forumUninstallValidator->expects($this->once())
      ->method('getForumVocabulary')
      ->willReturn($vocabulary);

    $this->forumUninstallValidator->expects($this->once())
      ->method('hasTermsForVocabulary')
      ->willReturn(FALSE);

    $module = 'forum';
    $expected = [
      'To uninstall Forum, first delete all <em>Forum</em> content',
    ];
    $reasons = $this->forumUninstallValidator->validate($module);
    $this->assertEquals($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidateHasTermsForVocabularyWithNodesAccess(): void {
    $this->forumUninstallValidator->expects($this->once())
      ->method('hasForumNodes')
      ->willReturn(TRUE);

    $url = $this->prophesize(Url::class);
    $url->toString()->willReturn('/path/to/vocabulary/overview');

    $vocabulary = $this->createMock('Drupal\taxonomy\VocabularyInterface');
    $vocabulary->expects($this->once())
      ->method('label')
      ->willReturn('Vocabulary label');
    $vocabulary->expects($this->once())
      ->method('toUrl')
      ->willReturn($url->reveal());
    $vocabulary->expects($this->once())
      ->method('access')
      ->willReturn(TRUE);
    $this->forumUninstallValidator->expects($this->once())
      ->method('getForumVocabulary')
      ->willReturn($vocabulary);

    $this->forumUninstallValidator->expects($this->once())
      ->method('hasTermsForVocabulary')
      ->willReturn(TRUE);

    $module = 'forum';
    $expected = [
      'To uninstall Forum, first delete all <em>Forum</em> content',
      'To uninstall Forum, first delete all <a href="/path/to/vocabulary/overview"><em class="placeholder">Vocabulary label</em></a> terms',
    ];
    $reasons = $this->forumUninstallValidator->validate($module);
    $this->assertEquals($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidateHasTermsForVocabularyWithNodesNoAccess(): void {
    $this->forumUninstallValidator->expects($this->once())
      ->method('hasForumNodes')
      ->willReturn(TRUE);

    $vocabulary = $this->createMock('Drupal\taxonomy\VocabularyInterface');
    $vocabulary->expects($this->once())
      ->method('label')
      ->willReturn('Vocabulary label');
    $vocabulary->expects($this->never())
      ->method('toUrl');
    $vocabulary->expects($this->once())
      ->method('access')
      ->willReturn(FALSE);
    $this->forumUninstallValidator->expects($this->once())
      ->method('getForumVocabulary')
      ->willReturn($vocabulary);

    $this->forumUninstallValidator->expects($this->once())
      ->method('hasTermsForVocabulary')
      ->willReturn(TRUE);

    $module = 'forum';
    $expected = [
      'To uninstall Forum, first delete all <em>Forum</em> content',
      'To uninstall Forum, first delete all <em class="placeholder">Vocabulary label</em> terms',
    ];
    $reasons = $this->forumUninstallValidator->validate($module);
    $this->assertEquals($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidateHasTermsForVocabularyAccess(): void {
    $this->forumUninstallValidator->expects($this->once())
      ->method('hasForumNodes')
      ->willReturn(FALSE);

    $url = $this->prophesize(Url::class);
    $url->toString()->willReturn('/path/to/vocabulary/overview');

    $vocabulary = $this->createMock('Drupal\taxonomy\VocabularyInterface');
    $vocabulary->expects($this->once())
      ->method('toUrl')
      ->willReturn($url->reveal());
    $vocabulary->expects($this->once())
      ->method('label')
      ->willReturn('Vocabulary label');
    $vocabulary->expects($this->once())
      ->method('access')
      ->willReturn(TRUE);
    $this->forumUninstallValidator->expects($this->once())
      ->method('getForumVocabulary')
      ->willReturn($vocabulary);

    $this->forumUninstallValidator->expects($this->once())
      ->method('hasTermsForVocabulary')
      ->willReturn(TRUE);

    $module = 'forum';
    $expected = [
      'To uninstall Forum, first delete all <a href="/path/to/vocabulary/overview"><em class="placeholder">Vocabulary label</em></a> terms',
    ];
    $reasons = $this->forumUninstallValidator->validate($module);
    $this->assertEquals($expected, $reasons);
  }

  /**
   * @covers ::validate
   */
  public function testValidateHasTermsForVocabularyNoAccess(): void {
    $this->forumUninstallValidator->expects($this->once())
      ->method('hasForumNodes')
      ->willReturn(FALSE);

    $vocabulary = $this->createMock('Drupal\taxonomy\VocabularyInterface');
    $vocabulary->expects($this->once())
      ->method('label')
      ->willReturn('Vocabulary label');
    $vocabulary->expects($this->never())
      ->method('toUrl');
    $vocabulary->expects($this->once())
      ->method('access')
      ->willReturn(FALSE);
    $this->forumUninstallValidator->expects($this->once())
      ->method('getForumVocabulary')
      ->willReturn($vocabulary);

    $this->forumUninstallValidator->expects($this->once())
      ->method('hasTermsForVocabulary')
      ->willReturn(TRUE);

    $module = 'forum';
    $expected = [
      'To uninstall Forum, first delete all <em class="placeholder">Vocabulary label</em> terms',
    ];
    $reasons = $this->forumUninstallValidator->validate($module);
    $this->assertEquals($expected, $reasons);
  }

}
