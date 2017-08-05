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
 * Testimonials
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Testimonials
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
     * @ORM\Column(name="author", type="string", length=50)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="testimonial", type="string", length=300)
     */
    private $testimonial;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=500)
     */
    private $filename;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @var boolean
     *
     * @ORM\Column(name="display", type="boolean")
     */
    private $display;

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

    public $url = '/admin/testimonials';
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
     * Set author
     *
     * @param string $author
     * @return Testimonials
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set testimonial
     *
     * @param string $testimonial
     * @return Testimonials
     */
    public function setTestimonial($testimonial)
    {
        $this->testimonial = $testimonial;

        return $this;
    }

    /**
     * Get testimonial
     *
     * @return string 
     */
    public function getTestimonial()
    {
        return $this->testimonial;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Testimonials
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Testimonials
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
     * Set display
     *
     * @param boolean $display
     * @return Testimonials
     */
    public function setDisplay($display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * Get display
     *
     * @return boolean 
     */
    public function getDisplay()
    {
        return $this->display;
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
        return 'uploads/testimonial';
    }

    public function getThumbUploadDir()
    {
        return 'uploads/testimonial/thumbs';
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
