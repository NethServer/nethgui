<?php
/**
 * @package NethGuiFramework
 */

/**
 * A Multiple adapter maps a scalar value to multiple keys or props through
 * a "reader" and a "writer" callback function.
 *
 * @package NethGuiFramework
 * @subpackage Core
 */
class NethGui_Core_MultipleAdapter implements NethGui_Core_AdapterInterface
{

    private $serializers = array();
    private $readerCallback;
    private $writerCallback;
    private $modified;

    /**
     * @see NethGui_Core_SerializerInterface
     * @param callback $readerCallback The reader PHP callback function: (p1, ..., pN) -> V
     * @param callback $writerCallback The writer PHP callback function: V -> (p1, ..., pN)
     * @param array $serializers An array of NethGui_Core_SerializerInterface objects
     */
    public function __construct($readerCallback, $writerCallback, $serializers)
    {
        if (empty($serializer)) {
            throw new NethGui_Exception_Adapter('Must provide one serializer, at least.');
        }

        if ( ! is_callable($readerCallback)) {
            throw new NethGui_Exception_Adapter('Must provide a Reader callback function');
        }

        $this->readerCallback = $readerCallback;

        if ( ! is_callable($writerCallback)) {
            throw new NethGui_Exception_Adapter('Must provide a Reader callback function');
        }

        $this->writerCallback = $writerCallback;

        foreach ($serializers as $serializer) {
            if ( ! $serializer instanceof NethGui_Core_SerializerInterface) {
                throw new NethGui_Exception_Adapter('Invalid serializer instance. A serializer must implement NethGui_Core_SerializerInterface.');
            }

            $this->serializers[] = $serializer;
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
        if ( ! $this->isModified()) {
            return;
        }

        $values = call_user_func($this->writerCallback, $this->value);
        
        $index = 0;
        
        foreach($values as $value) {
            $this->serializers[$index]->write($value);            
            $index++;
        }
        
    }

    private function lazyInitialization()
    {
        $values = array();
        foreach ($this->serializers as $serializer) {
            $values[] = $serializer->read();
        }

        $this->value = call_user_func_array($this->readerCallback, $values);
        $this->modified = FALSE;
    }

}