<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\CustomClasses\Thumbnail;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Offer
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Offer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="detail1", type="string", length=200)
     */
    private $detail1;

    /**
     * @var string
     *
     * @ORM\Column(name="detail2", type="string", length=300)
     */
    private $detail2;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    public $url = '/admin/offer';
    public $colspan = 6;

    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Offer
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set detail1
     *
     * @param string $detail1
     * @return Offer
     */
    public function setDetail1($detail1)
    {
        $this->detail1 = $detail1;

        return $this;
    }

    /**
     * Get detail1
     *
     * @return string 
     */
    public function getDetail1()
    {
        return $this->detail1;
    }

    /**
     * Set detail2
     *
     * @param string $detail2
     * @return Offer
     */
    public function setDetail2($detail2)
    {
        $this->detail2 = $detail2;

        return $this;
    }

    /**
     * Get detail2
     *
     * @return string 
     */
    public function getDetail2()
    {
        return $this->detail2;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Offer
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
}
