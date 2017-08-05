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
 * Banner
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Banner
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
     * @ORM\Column(name="page", type="string", length=20)
     */
    private $page;

    /**
     * @var string
     *
     * @ORM\Column(name="main_quote", type="string", length=30)
     */
    private $mainQuote;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_quote", type="string", length=50)
     */
    private $subQuote;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=1000, nullable=true)
     */
    private $fileName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled;

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

    public $url = '/admin/banner';
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
     * Set name
     *
     * @param string $name
     * @return Banner
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set mainQuote
     *
     * @param string $mainQuote
     * @return Banner
     */
    public function setMainQuote($mainQuote)
    {
        $this->mainQuote = $mainQuote;

        return $this;
    }

    /**
     * Get mainQuote
     *
     * @return string 
     */
    public function getMainQuote()
    {
        return $this->mainQuote;
    }

    /**
     * Set subQuote
     *
     * @param string $subQuote
     * @return Banner
     */
    public function setSubQuote($subQuote)
    {
        $this->subQuote = $subQuote;

        return $this;
    }

    /**
     * Get subQuote
     *
     * @return string 
     */
    public function getSubQuote()
    {
        return $this->subQuote;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return Banner
     */
    public function setfileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return Banner
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean 
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    public function getAbsolutePath()
    {
        return null === $this->fileName ? null : $this->getUploadRootDir().'/'.$this->fileName;
    }

    public function getThumbsAbsolutePath()
    {
        return null === $this->fileName ? null : $this->getThumbUploadRootDir().'/'.$this->fileName;
    }

    public function getWebPath()
    {
        return null === $this->fileName ? null : $this->getUploadDir().'/'.$this->fileName;
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
        return 'uploads/banner';
    }

    public function getThumbUploadDir()
    {
        return 'uploads/banner/thumbs';
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
        

        $this->file->move( $this->getUploadRootDir(), $this->fileName );

        // set the path property to the filename where you've saved the file
        //$this->fileName = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    public function createThumbnail()
    {       
        $newthumbnail = new Thumbnail();
        $newthumbnail->setUploadDirectory($this->getUploadRootDir() . '/');
        $newthumbnail->setThumbsize(100);
        $newthumbnail->setThumbdir($this->getThumbUploadRootDir() . '/');
        $newthumbnail->createThumbnail($this->fileName);       
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
            $this->fileName = $filename_new . '.' . $this->file->guessExtension();
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
