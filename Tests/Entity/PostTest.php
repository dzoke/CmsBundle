<?php

namespace Opifer\CmsBundle\Entity;

use Faker\Provider\DateTime;
use Opifer\CmsBundle\Entity\Post;
use Opifer\EavBundle\Model\ValueSetInterface;
use Opifer\EavBundle\Model\TemplateInterface;

class PostTest extends \PHPUnit_Framework_TestCase
{
    public function testValueSet()
    {
        $post = new Post();
        $valueSet = new ValueSet();

        $expected = $valueSet;
        $post->setValueSet($valueSet);
        $actual = $post->getValueSet();

        $this->assertSame($expected, $actual);
    }

    public function testSubmittedAt()
    {
        $post = new Post();
        $submittedAt = DateTime::dateTime();

        $expected = $submittedAt;
        $post->setSubmittedAt($submittedAt);
        $actual = $post->getSubmittedAt();

        $this->assertSame($expected, $actual);
    }

    public function testDeletedAt()
    {
        $post = new Post();
        $deletedAt = DateTime::dateTime();

        $expected = $deletedAt;
        $post->setDeletedAt($deletedAt);
        $actual = $post->getDeletedAt();

        $this->assertSame($expected, $actual);
    }

    public function testTemplate()
    {
        $post = new Post();
        $template = new Template();

        $expected = $template;
        $post->setTemplate($template);
        $actual = $post->getTemplate();

        $this->assertSame($expected, $actual);
    }

}