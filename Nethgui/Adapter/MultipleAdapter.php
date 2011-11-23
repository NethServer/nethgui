<?php
/**
 * @package Adapter
 */

/**
 * A Multiple adapter maps a scalar value to multiple keys or props through
 * a "reader" and a "writer" callback function.
 * 
 * The reader function is mandatory, the writer is optional. If you set NULL the writer
 * callback you get a read-only adapter. All save() calls have no effects and returns
 * int(0) changes.
 * 
 * If the adapter has an empty set of serializers the callback function will 
 * still be called with no arguments.
 *
 * @package Adapter
 */
class Nethgui\Adapter\MultipleAdapter implements Nethgui\Adapter\AdapterInterface
{

    private $innerAdapters = array();
    private $readerCallback;
    private $writerCallback;
    private $modified;

    /**
     * @see Nethgui\Serializer\SerializerInterface
     * @param callback $readerCallback The reader PHP callback function: (p1, ..., pN) -> V
     * @param callback $writerCallback The writer PHP callback function: V -> (p1, ..., pN)
     * @param array $serializers An array of Nethgui\Serializer\SerializerInterface objects
     */
    public function __construct($readerCallback, $writerCallback = NULL, $serializers = array())
    {
        if ( ! is_callable($readerCallback)) {
            throw new InvalidArgumentException('Must provide a Reader callback function');
        }

        $this->readerCallback = $readerCallback;
        $this->writerCallback = $writerCallback;

        foreach ($serializers as $serializer) {
            if ( ! $serializer instanceof Nethgui\Serializer\SerializerInterface) {
                throw new Nethgui\Exception\Adapter('Invalid serializer instance. A serializer must implement Nethgui\Serializer\SerializerInterface.');
            }

            $this->innerAdapters[] = new Nethgui\Adapter\ScalarAdapter($serializer);
        }
    }

    public function get()
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        return $this->value;
    }

    public function set($value)
    {
        if (is_null($this->modified)) {
            $this->lazyInitialization();
        }

        if ($this->value !== $value) {
            $this->value = $value;
            $this->modified = TRUE;
        }
    }

    public function delete()
    {
        $this->set(NULL);
    }

    public function isModified()
    {
        return $this->modified === TRUE;
    }

    public function save()
    {
        if ( ! is_callable($this->writerCallback)) {
            return 0;
        }

        if ( ! $this->isModified()) {
            return 0;
        }

        $values = call_user_func($this->writerCallback, $this->value);

        $index = 0;
        $changes = 0;

        if (is_array($values)) {
            foreach ($values as $value) {
                $this->innerAdapters[$index]->set($value);
                $changes += $this->innerAdapters[$index]->save();
                $index ++;
            }
        }

        $this->modified = FALSE;

        return $changes;
    }

    private function lazyInitialization()
    {
        $values = array();
        foreach ($this->innerAdapters as $innerAdapter) {
            $values[] = $innerAdapter->get();
        }

        $this->value = call_user_func_array($this->readerCallback, $values);
        $this->modified = FALSE;
    }

}