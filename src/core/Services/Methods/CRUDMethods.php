<?php

namespace Steak\Core\Services\Methods;

use Illuminate\Http\Request;
use ReflectionClass;
use Steak\Core\Validators\ExampleValidator;
use Steak\Core\Repositories\BaseRepository;

trait CRUDMethods
{
     /**
     * @var string $validatorClass
     * full class name 
     */
    protected $validatorClass = 'ExampleValidator';

    /**
     *
     * @var array
     */
    protected $validateAttrs = [];
    /**
     * validator namespace
     *
     * @var string
     */
    protected $validatorNamespace = 'Steak\Core\Validators';


    /**
     * app validator namespace
     *
     * @var string
     */
    protected $appNamespace = 'App\Validators';

    /**
     * validator repository
     *
     * @var \Steak\Core\Repositories\BaseRepository
     */
    protected $validatorRepository = null;


    /**
     * @var \Steak\Core\Repositories\BaseRepository $repository
     */
    protected $repository = null;


    /**
     * @var string $repositoryClass
     * full class name 
     */
    protected $repositoryClass = '';


    public function initCRUD(){
        if($this->repositoryClass){
            $this->setRepositoryClass($this->repositoryClass);
        }
        return $this;
    }



    public function setRepositoryClass($repositoryClass)
    {
        if(is_string($repositoryClass) && class_exists($repositoryClass)){
            $this->repositoryClass = $repositoryClass;
        }

        return $this;
    }


    public function setRepository($repository){
        if(is_object($repository) && ($repository instanceof BaseRepository || is_a($repository, BaseRepository::class))){
            $this->repository = $repository;
        }
        elseif(is_string($repository) && class_exists($repository)){
            $this->repository = app($repository);
        }
        return $this;
    }

    public function getRepository(){
        return $this->repository??$this->repositoryClass?app($this->repositoryClass):null;
    }
    /**
     * set validator repository
     *
     * @param \Steak\Core\Repositories\BaseRepository $validatorRepository
     * @return $this instance
     */
    public function setValidatorRepository($validatorRepository)
    {
        if(is_object($validatorRepository) && ($validatorRepository instanceof BaseRepository || is_a($validatorRepository, BaseRepository::class))){
            $this->validatorRepository = $validatorRepository;
        }
        elseif(is_string($validatorRepository) && class_exists($validatorRepository)){
            $this->validatorRepository = app($validatorRepository);
        }
        return $this;
    }

    public function getValidatorRepository()
    {
        return $this->validatorRepository??$this->repository??$this->repositoryClass?app($this->repositoryClass):null;
    }

    /**
     * dat validator class
     * @param string $validatorClass tên class
     * @return $this instance
     */
    public function setValidatorClass($validatorClass)
    {
        if (class_exists($validatorClass)) {
            $this->validatorClass = $validatorClass;
        } elseif (class_exists($validatorClass . 'Validator')) {
            $this->validatorClass = $validatorClass . 'Validator';
        } elseif (class_exists($this->appNamespace . "\\" . $validatorClass)) {
            $this->validatorClass = $this->appNamespace . "\\" . $validatorClass;
        } elseif (class_exists($this->appNamespace . "\\" . $validatorClass . 'Validator')) {
            $this->validatorClass = $this->appNamespace . "\\" . $validatorClass . 'Validator';
        } elseif (class_exists($this->validatorNamespace . "\\" . $validatorClass)) {
            $this->validatorClass = $this->validatorNamespace . "\\" . $validatorClass;
        } elseif (class_exists($this->validatorNamespace . "\\" . $validatorClass . 'Validator')) {
            $this->validatorClass = $this->validatorNamespace . "\\" . $validatorClass . 'Validator';
        }
        return $this;
    }




    /**
     * lay doi tuong validator
     * @param Request $request
     * @param string $validatorClass
     * @return ExampleValidator
     */
    public function getValidator(Request $request, $validatorClass = null)
    {
        if ($validatorClass) {
            $this->setValidatorClass($validatorClass);
        }
        $this->fire('beforegetvalidator', $this, $request);
        if ($this->validatorClass) {
            $c = null;

            if (class_exists($this->validatorClass)) {
                $c = $this->validatorClass;
            } elseif (class_exists($class = $this->validatorNamespace . '\\' . $this->validatorClass)) {
                $c = $class;
            } else {
                $c = 'Steak\Core\Validators\ExampleValidator';
            }
            $rc = new ReflectionClass($c);
            return $rc->newInstanceArgs([$request, $this]);
        }
        return new ExampleValidator($request, $this);
    }

    /**
     *
     * lay doi tuong validator
     * @return Validator
     */
    public function validator(Request $request, $validatorClass = null)
    {
        $this->fire('beforevalidator', $this, $request);
        $validator = $this->getValidator($request, is_string($validatorClass) ? $validatorClass : null);
        $validator->check(is_array($validatorClass) ? $validatorClass : []);
        return $validator;
    }

    /**
     * lay du lieu da duoc validate
     * @param Request $request
     * @param string|array $ruleOrvalidatorClass
     * @param array $messages
     * @return array
     */
    public function validate(Request $request, $ruleOrvalidatorClass = null, $messages = [])
    {
        $this->fire('beforevalidate', $this, $request);
        return $this->getValidator(
            $request,
            is_string($ruleOrvalidatorClass) ? $ruleOrvalidatorClass : null
        )->validate(
            is_array($ruleOrvalidatorClass) ? $ruleOrvalidatorClass : [],
            is_array($messages) ? $messages : []
        );
    }

    /**
     * lay du lieu da duoc validate
     * @param Request $Request
     * @param string|array $ruleOrvalidatorClass
     * @param array $messages
     * @return array
     */
    public function getValidateData(Request $request, $ruleOrvalidatorClass = null, $messages = [])
    {
        return $this->validate($request, $ruleOrvalidatorClass, $messages);
    }


    public function setValidatoAttrs(...$attrs)
    {
        if (is_array($attrs) && count($attrs)) {
            foreach ($attrs as $attr) {
                if (is_string($attr)) {
                    if ($attr == '*') {
                        $this->validateAttrs = '*';
                        return;
                    }
                    $this->validateAttrs[] = $attr;
                } elseif (is_array($attr)) {
                    $this->validateAttrs = array_merge($this->validateAttrs, $attr);
                }
            }
        }
    }

    public function getValidateAttrs()
    {
        if (is_array($this->validateAttrs) && count($this->validateAttrs)) {
            return $this->validateAttrs;
        }
        return null;
    }

}