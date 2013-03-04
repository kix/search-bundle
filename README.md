Kix\SearchBundle
================
This is a bundle intended to prettify and ease the process of setting up a Solr search backend in a Symfony2
application.

Usage:
------
In an entity class you intend to index, mark the class and needed fields as indexed with the annotaion:

```php
<?php

namespace My\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kix\SearchBundle\Annotation as Search;

/**
 * Work order
 *
 * @package Orders
 *
 * @ORM\Table(name="orders")
 * @ORM\Entity
 * @Search\Indexed(type="workOrder")
 */
class WorkOrder
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Search\Id()
     * @Search\Indexed(type="int")
     */
    private $id;

    /**
     * @var string
     *
     * @Search\Indexed(type="string")
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @Search\Indexed(type="text_general")
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @Search\Indexed(type="date")
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var User
     *
     * @Search\ComputedIndexed(
     *      types={
     *          "authorId": "int",
     *          "authorName": "string"
     *      }, calls={
     *          "authorId": "getId",
     *          "authorName": "getFullName"
     *      }
     * )
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $author;

    /**
     * @var City
     * @ORM\ManyToOne(targetEntity="City")
     *
     * @Search\ComputedIndexed(
     *      types={
     *          "city":   "string",
     *          "cityId": "int"
     *      }, calls={
     *          "city":   "getName",
     *          "cityId": "getId"
     *      }
     * )
     */
    private $city;

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
     * @param User $author
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return WorkOrder
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return WorkOrder
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getCity()
    {
        return $this->city;
    }
}
```

As you could notice, there are several kinds of ```Indexed```: just the plain ol' ```Indexed```, and ```ComputedIndexed```.
The difference is that a ```ComputedIndexed``` field works with entities associated through the property rather than the
propreties themselves. This leads to some extra configuration being needed:
```php
@Search\ComputedIndexed(
     types={
         "city":   "string", <-- the key "city" is the field name, "string" is the type
         "cityId": "int"     <-- same, "cityId" is the name, the field type is "int"
     }, calls={
         "city":   "getName", <-- this is the method name to call on the property for this key
         "cityId": "getId"
     }
)
*/
private $city;
```
So, what sense does it make? It specifies that for fetching the data for indexing and storing it into two Solr fields
we need to make two calls on the property that's annotated.

Updates on indexed entities are then captured in the ```SearchListener``` class.

Now, to index the entities, you need to get the ```OldSound\RabbitMqBundle``` to work.