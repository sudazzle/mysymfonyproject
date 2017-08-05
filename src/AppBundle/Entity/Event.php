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
 * Event
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Event
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
     * @ORM\Column(name="detail", type="string", length=1000)
     */
    private $detail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="eventdate", type="datetime")
     */
    private $eventdate;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=500)
     */
    private $filename;

    /**    
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 1000,
     *     minHeight = 200,
     *     maxHeight = 400,
     *     maxSize = 6000000
     * )
     */
    private $file;

    public $url = '/admin/event';
    public $minWidth = 200;  
    public $maxWidth = 1000;
    public $minHeight = 200;
    public $maxHeight = 400;
    public $maxSize = 6000000;
    public $colspan = 9;


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
     * @return Event
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
     * Set detail
     *
     * @param string $detail
     * @return Event
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get detail
     *
     * @return string 
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Event
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

    /**
     * Set eventdate
     *
     * @param \DateTime $eventdate
     * @return Event
     */
    public function setEventdate($eventdate)
    {
        $this->eventdate = $eventdate;

        return $this;
    }

    /**
     * Get eventdate
     *
     * @return \DateTime 
     */
    public function getEventdate()
    {
        return $this->eventdate;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Lesson
     */
    public function setfilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getfilename()
    {
        return $this->filename;
    }

    public function getAbsolutePath()
    {
        return null === $this->filename ? null : $this->getUploadRootDir().'/'.$this->filename;
    }

    public function getThumbsAbsolutePath()
    {
        return null === $this->filename ? null : $this->getThumbUploadRootDir().'/'.$this->filename;
    }

    public function getWebPath()
    {
        return null === $this->filename ? null : $this->getUploadDir().'/'.$this->filename;
    }

    public function getUploadRootDir()
    {
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function getThumbUploadRootDir()
    {
        return __DIR__.'/../../../web/'.$this->getThumbUploadDir();
    }

    public function getUploadDir()
    {
        return 'uploads/event';
    }

    public function getThumbUploadDir()
    {
        return 'uploads/event/thumbs';
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
    */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
    */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Called after entity persistance
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
    */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if(null === $this->getFile())
        {
            return;
        }

        // use the original file name here but you should 
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the 
        // target filename to move to

        //echo new Response($this->getFile()->getClientOriginalName());       
            $this->preUpload();
        

        $this->file->move( $this->getUploadRootDir(), $this->filename );

        // set the path property to the filename where you've saved the file
        //$this->filename = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    public function createThumbnail()
    {       
        $newthumbnail = new Thumbnail();
        $newthumbnail->setUploadDirectory($this->getUploadRootDir() . '/');
        $newthumbnail->setThumbsize(100);
        $newthumbnail->setThumbdir($this->getThumbUploadRootDir() . '/');
        $newthumbnail->createThumbnail($this->filename);       
    }

    /**
     * Called before saving the entity
     * @ORM\PrePersist()
     * @ORM\PreUpdate()     
    */

    public function preUpload()
    {
        if(null !== $this->file)
        {
            //do whatever you want to generate a unique name
            $filename_new = sha1(uniqid(mt_rand(), true));
            $this->filename = $filename_new . '.' . $this->file->guessExtension();
        }
    }

    /**
     * Called before entity removal
     * @ORM\PreRemove()
    */
    public function removeUpload()
    {
        if($file = $this->getAbsolutePath())
        {
            unlink($file);
        }
    }  
    /**
     * Called before entity removal
     * @ORM\PreRemove()
    */
    public function removeThumbs()
    {
        if($file = $this->getThumbsAbsolutePath())
        {
            unlink($file);
        }
    } 
}
