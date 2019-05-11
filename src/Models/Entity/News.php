<?php
namespace App\Models\Entity;
/**
 * @Entity @Table(name="news")
 **/
class News {
    /**
     * @var int
     * @Id @Column(type="integer") 
     * @GeneratedValue
     */
    public $id;
    /**
     * @var string
     * @Column(type="string",length=80) 
     */
    public $title;

    /**
     * @var string
     * @Column(type="text") 
     */
    public $description;
    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    public $author;

    /** @Column(type="datetime") */
    public $created;

    /** @Column(type="datetime", nullable=true) */
    private $updated;

    /** @Column(type="boolean", nullable=true, options={"default":true}) */
    public $status;

    public function __construct()
    {
        $this->created = new \DateTime("now");
        $this->status = true;
    }

    
    public function getId(){
        return $this->id;
    }
    public function getTitle(){
        return $this->title;
    }
    public function getAuthor() {
        return $this->author;
    } 

    public function getCreated()
    {        
        return $this->created;
    }

    public function getUpdated()
    {        
        return $this->updated;
    }

    public function getStatus()
    {        
        return $this->status;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getValues(){
        return (array)$this;
    }

    public function setTitle($title){
        if (!$title && !is_string($title)) {
            throw new \InvalidArgumentException("Title is required", 400);
        }
        $this->title = $title;
        return $this;  
    }

    public function setDescription($description){
        $this->description = $description;
        return $this;
    }

    public function setAuthor($author) {
        if (!$author) {
            throw new \InvalidArgumentException("Author is required", 400);
        }
        $this->author = $author;
        return $this;
    }

    public function setUpdated()
    {
        $this->updated = new \DateTime("now");
        return $this;
    }

    public function setStatus($status)
    {       
        $this->status = ($status==='false')? false:true;        
        return $this;
    }
}