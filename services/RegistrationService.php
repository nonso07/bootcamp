<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Parent.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Payment.php';

class RegistrationService
{
    private PDO $pdo;
    private ParentModel $parentModel;
    private StudentModel $studentModel;
    private RegistrationModel $registrationModel;
    private InvoiceModel $invoiceModel;
    private PaymentModel $paymentModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->parentModel = new ParentModel($pdo);
        $this->studentModel = new StudentModel($pdo);
        $this->registrationModel = new RegistrationModel($pdo);
        $this->invoiceModel = new InvoiceModel($pdo);
        $this->paymentModel = new PaymentModel($pdo);
    }

    /**
     * Handle the full registration flow inside one database transaction.
     */
    public function register(array $input): array
    {
        $this->pdo->beginTransaction();

        try {
            $requiredFields = [
                'first_name', 'last_name', 'gender', 'dob', 'school', 'class_level', 'nationality', 'address',
                'parent_name', 'relationship', 'phone', 'email', 'course', 'session', 'tshirt_size', 'payment_method'
            ];

            $errors = validateRequired($input, $requiredFields);
            if ($errors) {
                throw new InvalidArgumentException('Missing required fields: ' . implode(', ', $errors));
            }

            $photoName = null;
            if (!empty($_FILES['photo']['name'])) {
                $photoName = uploadStudentPhoto($_FILES['photo'], __DIR__ . '/../uploads/students');
            }

            $parentId = $this->parentModel->create([
                'name' => sanitize($input['parent_name']),
                'relationship' => sanitize($input['relationship']),
                'occupation' => sanitize($input['occupation'] ?? ''),
                'phone' => sanitize($input['phone']),
                'whatsapp' => sanitize($input['whatsapp'] ?? ''),
                'email' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
                'emergency_contact' => sanitize($input['emergency_contact'] ?? ''),
            ]);

            $studentRegistrationNo = generateStudentRegistrationNumber($this->pdo);
            $studentId = $this->studentModel->create([
                'registration_no' => $studentRegistrationNo,
                'parent_id' => $parentId,
                'first_name' => sanitize($input['first_name']),
                'last_name' => sanitize($input['last_name']),
                'gender' => sanitize($input['gender']),
                'dob' => sanitize($input['dob']),
                'school_id' => null,
                'class_level' => sanitize($input['class_level']),
                'nationality' => sanitize($input['nationality']),
                'address' => sanitize($input['address']),
                'photo' => $photoName,
            ]);

            $courseFee = (float) ($input['course_fee'] ?? 30000);
            $paymentStatus = 'Pending';
            $registrationId = $this->registrationModel->create([
                'student_id' => $studentId,
                'course_id' => (int) $input['course_id'],
                'bootcamp_id' => null,
                'tshirt_size' => sanitize($input['tshirt_size']),
                'session' => sanitize($input['session']),
                'amount' => $courseFee,
                'payment_status' => $paymentStatus,
            ]);

            $invoiceNumber = generateInvoiceNumber($this->pdo);
            $invoiceId = $this->invoiceModel->create([
                'invoice_no' => $invoiceNumber,
                'registration_id' => $registrationId,
                'invoice_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d'),
                'subtotal' => $courseFee,
                'discount' => 0.00,
                'total' => $courseFee,
                'payment_status' => 'pending',
            ]);

            $this->invoiceModel->addItem($invoiceId, $courseFee);

            $paymentMethod = sanitize($input['payment_method']);
            $paymentGateway = $paymentMethod === 'paystack' ? 'Paystack' : 'Manual';
            $paymentStatus = 'Pending';

            $paymentId = $this->paymentModel->create([
                'registration_id' => $registrationId,
                'amount' => $courseFee,
                'currency' => 'NGN',
                'payment_method' => $paymentMethod,
                'payment_gateway' => $paymentGateway,
                'transaction_reference' => null,
                'cinetpay_transaction_id' => null,
                'status' => $paymentStatus,
                'gateway_response' => null,
                'paid_at' => null,
            ]);

            createActivityLog($this->pdo, 'New student registered');

            $this->pdo->commit();

            $redirect = $paymentMethod === 'paystack'
                ? 'paystack_payment.php?id=' . $paymentId
                : 'invoice.php?id=' . $invoiceId;

            return [
                'success' => true,
                'registration_number' => $studentRegistrationNo,
                'invoice_number' => $invoiceNumber,
                'student_id' => $studentId,
                'invoice_id' => $invoiceId,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'redirect' => $redirect,
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            logError($e->getMessage() . ' | ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Registration failed.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
