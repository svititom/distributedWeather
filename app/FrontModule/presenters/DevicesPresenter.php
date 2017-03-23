<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 18-Mar-17
 * Time: 00:01
 */

namespace App\FrontModule\Presenters;
use App\Model\DuplicateNameException;
use App\Entities\DeviceManager;
use App\Model\UserManager;


class DevicesPresenter extends BaseSecurePresenter
{
    /** @var \Instante\ExtendedFormMacros\IFormFactory @inject */
    public $formFactory;

    /** @var  DeviceManager @inject*/
    public $deviceManager;

    /** @var UserManager @inject*/
    public $userManager;

    public function isNullOrZero($value){
        return $value == null || $value == 0;
    }

    protected function createComponentAddDeviceForm(){

        $form = $this->formFactory->create();
        $form->addText('name', 'Device name', null, 30)
            ->setRequired('Please type a name for this device');
        $form->addText('tempAccuracy', 'Temperature measurement accuracy (in °C)')
            ->setDefaultValue(0);
        $form->addText('humidAccuracy', 'Humidity measurement accuracy (in %RH)')
            ->setDefaultValue(0);
        $form->addText('presAccuracy', 'Pressure measurement accuracy (in hPa)')
            ->setDefaultValue(0);

        $form->addText('model', 'Sensor model number(s)');
        $form->addText('vendor', 'Device vendor'); //Todo find a better way to name this



        //todo add rule to make sure at least one accuracy is non zero

        $form->addSubmit('add', 'Add device');
        $form->addSubmit('cancel', 'Cancel')
            ->setValidationScope(array())
            ->onClick[] = function ($sender){
                $this->redirect('Devices:');
        };
        $form->onSuccess[] = function ($form, $values){
            if ($values->name == null){
                $form->addError("You need to choose a name for the device");
                return;
            }
            if ($this->isNullOrZero($values->tempAccuracy)
                && $this->isNullOrZero($values->humidAccuracy)
                &&   $this->isNullOrZero($values->presAccuracy)){
                $form->addError("At least one accuracy must be a positive number");
                return;
            }
            $userId = $this->getUser()->getId();
            try {

                $this->deviceManager->addDevice($userId, $values->name, $values->model, $values->vendor,
                    $values->tempAccuracy, $values->humidAccuracy, $values->presAccuracy);
            } catch (DuplicateNameException $e){
                $form->addError("Please choose a device name you haven't already registered");
                return;
            }

            $this->redirect("Devices:");

        };
        return $form;
    }

    protected function createComponentDeleteDeviceForm()
    {
        $form = $this->formFactory->create();
        $form->addSubmit('delete', 'Delete device');
        $form->addSubmit('cancel', 'Cancel');
        $form->onSuccess[] = function ($form, $values){
            if ($form['cancel']->isSubmittedBy()){
                $this->redirect('Devices:');
            }
            $this->deviceManager->removeDevice($this->getParameter('deviceId'));
            $this->flashMessage('Deleting device No ' . $this->getParameter('deviceId'));
            $this->redirect('Devices:');
        };
        return $form;
    }



    public function actionDelete($deviceId){
    }
    public function renderEdit($deviceId){
        $this->template->device = $this->deviceManager->findDeviceById($deviceId);
    }
    public function renderDefault()
    {
        $this->template->devices = $this->userManager->getUserDevices($this->getUser()->getId());
    }
}