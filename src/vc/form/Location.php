<?php
namespace vc\form;

class Location extends FormElement
{
    private $objectStreetField;

    private $objectPostalField;

    private $objectCityField;

    private $objectRegionField;

    private $objectCountryField;

    private $countries;

    private $objectLatField;

    private $objectLngField;

    private $caption;

    public function __construct(
        $objectCaptionField,
        $objectStreetField,
        $objectPostalField,
        $objectCityField,
        $objectRegionField,
        $objectCountryField,
        $countries,
        $objectLatField,
        $objectLngField,
        $name,
        $caption
    ) {
        parent::__construct($objectCaptionField, $name);
        $this->objectStreetField = $objectStreetField;
        $this->objectPostalField = $objectPostalField;
        $this->objectCityField = $objectCityField;
        $this->objectRegionField = $objectRegionField;
        $this->objectCountryField = $objectCountryField;
        $this->countries = $countries;
        $this->objectLatField = $objectLatField;
        $this->objectLngField = $objectLngField;
        $this->caption = $caption;
    }

    protected function getRenderTemplate()
    {
        return 'location';
    }

    protected function getRenderParams()
    {
        $regions = \vc\config\Fields::getRegions();
        return array('caption' => $this->caption,
                     'default' => $this->getDefault(),
                     'class' => $this->getClass(),
                     'countries' => $this->countries,
                     'regions' => $regions);
    }

    public function validate($db, $formValues)
    {
        $isValid = parent::validate($db, $formValues);

        if (empty($formValues[$this->getName()]['caption'])) {
            $formCaptionValue = '';
        } else {
            $formCaptionValue = trim($formValues[$this->getName()]['caption']);
        }
        if (empty($formValues[$this->getName()]['city'])) {
            $formCityValue = '';
        } else {
            $formCityValue = trim($formValues[$this->getName()]['city']);
        }
        if (empty($formValues[$this->getName()]['country'])) {
            $formCountryValue = 0;
        } else {
            $formCountryValue = intval($formValues[$this->getName()]['country']);
        }

        if ($isValid) {
            if ($this->isMandatory() &&
                (empty($formCaptionValue) || empty($formCityValue) || empty($formCountryValue))) {
                $isValid = false;
                $this->validationErrors[] = gettext('form.missingMandatory');
            }
        }
        return $isValid;
    }

    public function getObjectValue(&$object, $formValues)
    {
        $objectField = $this->getObjectField();
        if ($objectField !== null) {
            if (array_key_exists($this->getName(), $formValues)) {
                $formValue = $formValues[$this->getName()];
                if (!empty($formValue['caption'])) {
                    $object->$objectField = trim($formValue['caption']);
                }
                if (!empty($formValue['street'])) {
                    $object->{$this->objectStreetField} = $formValue['street'];
                } else {
                    $object->{$this->objectStreetField} = '';
                }
                if (!empty($formValue['postal'])) {
                    $object->{$this->objectPostalField} = $formValue['postal'];
                } else {
                    $object->{$this->objectPostalField} = '';
                }
                if (!empty($formValue['city'])) {
                    $object->{$this->objectCityField} = $formValue['city'];
                } else {
                    $object->{$this->objectCityField} = '';
                }
                if (!empty($formValue['region'])) {
                    $object->{$this->objectRegionField} = $formValue['region'];
                } else {
                    $object->{$this->objectRegionField} = '';
                }
                if (!empty($formValue['country'])) {
                    $object->{$this->objectCountryField} = intval($formValue['country']);
                } else {
                    $object->{$this->objectCountryField} = 49;
                }
                if (!empty($formValue['lat'])) {
                    $object->{$this->objectLatField} = floatval($formValue['lat']);
                } else {
                    $object->{$this->objectLatField} = 0.0;
                }
                if (!empty($formValue['lng'])) {
                    $object->{$this->objectLngField} = floatval($formValue['lng']);
                } else {
                    $object->{$this->objectLngField} = 0.0;
                }
            }
        }
    }

    public function setObjectValue($object)
    {
        $default = array(
            'caption' => '',
            'street' => '',
            'postal' => '',
            'region' => '',
            'city' => '',
            // Default country
            'country' => 49,
            'lat' => 0,
            'lng' => 0
        );
        $objectField = $this->getObjectField();
        if ($objectField !== null && isset($object->$objectField)) {
            $default['caption'] = $object->$objectField;
        }
        if ($objectField !== null && isset($object->{$this->objectStreetField})) {
            $default['street'] = $object->{$this->objectStreetField};
        }
        if ($objectField !== null && isset($object->{$this->objectPostalField})) {
            $default['postal'] = $object->{$this->objectPostalField};
        }
        if ($objectField !== null && isset($object->{$this->objectCityField})) {
            $default['city'] = $object->{$this->objectCityField};
        }
        if ($objectField !== null && isset($object->{$this->objectRegionField})) {
            $default['region'] = $object->{$this->objectRegionField};
        }
        if ($objectField !== null && isset($object->{$this->objectCountryField})) {
            $default['country'] = $object->{$this->objectCountryField};
        }
        if ($this->objectLatField !== null && isset($object->{$this->objectLatField})) {
            $default['lat'] = $object->{$this->objectLatField};
        }
        if ($this->objectLngField !== null && isset($object->{$this->objectLngField})) {
            $default['lng'] = $object->{$this->objectLngField};
        }
        $this->setDefault($default);
    }
}
