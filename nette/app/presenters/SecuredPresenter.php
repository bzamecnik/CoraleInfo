<?php
abstract class SecuredPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        $this->ensureLoggedUser();
    }
}
