<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

namespace Topliner\Scheme;


use LanguageSpecific\ArrayHandler;

class Api
{
    /**
     * @var ArrayHandler
     */
    private $parameters;

    public function __construct(array $body)
    {
        $this->parameters = new ArrayHandler($body);
    }

    public function run()
    {
        $output = ['success' => false, 'message' => 'Call not found'];

        $call = $this->parameters->get('call')->str();
        $isValid = false;
        $isFound = false;
        switch ($call) {
            case Landmark::ADD_NEW:
                $isFound = true;
                $isValid = $this->parameters->has('x')
                    && $this->parameters->has('y')
                    && $this->parameters->has('type');
                break;
            case Landmark::STORE:
                $isFound = true;
                $isValid = $this->parameters->has('x')
                    && $this->parameters->has('y')
                    && $this->parameters->has('number');
                break;
            case Landmark::PUBLISH:
                $isFound = true;
                $isValid = $this->parameters->has('number');
                break;
            case Landmark::FLUSH :
            case Landmark::RECOMPILE:
            case Landmark::RELEASE :
                $isFound = true;
                $isValid = true;
                break;
        }
        if ($isValid) {
            $output = (new Landmark($this->parameters))->process();
        }
        if (!$isValid && $isFound) {
            $output['message'] = 'Parameters of call is not valid';
        }

        return $output;
    }
}