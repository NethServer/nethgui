<?php
/**
 * @package NethGuiFramework
 * @author Davide Principi <davide.principi@nethesis.it>
 */

/**
 * TODO describe this class
 * @package NethGuiFramework
 * @subpackage Core
 */
class NethGui_Core_MultipleAdapter implements NethGui_Core_AdapterInterface
{

    private $serializers = array();
    private $readerCallback;
    private $writerCallback;
    private $modified;

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

    public function delete()
    {

    }

    public function get()
    {
        if (is_null($this->modified)) {

            $values = array();
            foreach ($this->serializers as $serializer) {
                $values[] = $serializer->read();
            }

            $this->modified = FALSE;
            $this->value = call_user_func($this->readerCallback, $values);
        }

        return $this->value;
    }

    public function isModified()
    {
        return $this->modified === TRUE;
    }

    public function save()
    {

    }

    public function set($value)
    {

    }

}