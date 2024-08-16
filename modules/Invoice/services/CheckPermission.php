<?php

declare(strict_types=1);

final class Invoice_CheckPermission_Service
{
    public function __construct(
        protected ?int $invoiceId,
    ) {}

    public function isEditAllowed(): bool
    {
        if (!$this->invoiceId) {
            return true;
        }
        $user = Users_Record_Model::getCurrentUserModel();
        if (!$user && $user->isAdminUser()) {
            return true;
        }
        $invoiceModel = Invoice_Record_Model::getInstanceById($this->invoiceId);
        if (!$invoiceModel) {
            return true;
        }
        $date = $invoiceModel->get('invoicedate');
        if (!$date) {
            $date = $invoiceModel->get('createdtime');
        }

        try {
            $providedDate = new DateTimeImmutable($date);
            $currentDate = new DateTimeImmutable();

            // Compare the month and year of the provided date with the current date
            return $providedDate->format('Y-m') === $currentDate->format('Y-m');
        } catch (Exception $e) {
            return false;
        }
    }
}
