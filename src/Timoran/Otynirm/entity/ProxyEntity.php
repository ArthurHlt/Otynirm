<?php
namespace Timoran\Otynirm\entity;
class ProxyEntity extends AbstractEntity{
	private $entity;
	private $dirty = false;
	public function __construct($entity){
		$this->entity = $entity;
	}

	public function __call($name, $arguments){
		if(!empty($arguments)){
			$this->dirty = true;
		}
		return call_user_func_array(array($this->entity, $name), $arguments);
	}
	public function isDirty(){
		return $this->dirty;
	}
	public function setDirty($dirty) {
		$this->dirty = $dirty;
		return $this;
	}
	public function getEntity() {
		return $this->entity;
	}


}