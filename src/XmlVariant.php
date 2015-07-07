<?php
namespace FusionsPIM\DemandwareXml;

class XmlVariant extends XmlAbstract
{
    protected $element = 'variation-attribute';

    public function __construct($id = null)
    {
        $this->attributes = ['variation-attribute-id' => $id, 'attribute-id' => $id];
    }

    public function addTags($map = [])
    {
        $xml = '';

        foreach ($map as $id => $value) {
            $xml .= '<variation-attribute-value value="' . XmlDocument::escape($id) . '">
                        <display-value xml:lang="x-default">' . XmlDocument::escape($value) . '</display-value>
                    </variation-attribute-value>';
        }

        $this->elements['variation-attribute-values'] = $xml;
    }
}
