<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/Parent.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Registration.php';
require_once __DIR__ . '/../models/Invoice.php';

class RegistrationService
{
    private PDO $pdo;
    private ParentModel $parentModel;
    private StudentModel $studentModel;
    private RegistrationModel $registrationModel;
    private InvoiceModel $invoiceModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->parentModel = new ParentModel($pdo);
        $this->studentModel = new StudentModel($pdo);
        $this->registrationModel = new RegistrationModel($pdo);
        $this->invoiceModel = new InvoiceModel($pdo);
    }

    /**
     * Handle the full registration flow inside one database transaction.
     */
    public function register(array $input): array
    {
        $this->pdo->beginTransaction();

        try {
            $requiredFields = [
                'first_name', 'last_name', 'gender', 'dob', 'parent_name', 'phone', 'course_id', 'session', 'tshirt_size'
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
                'relationship' => sanitize($input['relationship'] ?? ''),
                'occupation' => sanitize($input['occupation'] ?? ''),
                'phone' => sanitize($input['phone']),
                'whatsapp' => sanitize($input['whatsapp'] ?? ''),
                'email' => filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL),
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
                'class_level' => sanitize($input['class_level'] ?? ''),
                'nationality' => sanitize($input['nationality'] ?? ''),
                'address' => sanitize($input['address'] ?? ''),
                'photo' => $photoName,
            ]);

            $courseId = isset($input['course_id']) ? (int) $input['course_id'] : 0;
            $coursePriceStmt = $this->pdo->prepare('SELECT course_price FROM courses WHERE course_id = ? LIMIT 1');
            $coursePriceStmt->execute([$courseId]);
            $courseRow = $coursePriceStmt->fetch(PDO::FETCH_ASSOC);
            if (!$courseRow) {
                throw new InvalidArgumentException('Invalid course selected.');
            }

            $courseFee = (float)$courseRow['course_price'];
            $paymentStatus = 'Pending';
            $invoiceNumber = generateInvoiceNumber($this->pdo);
            $registrationId = $this->registrationModel->create([
                'invoice_no' => $invoiceNumber,
                'student_id' => $studentId,
                'course_id' => $courseId,
                'bootcamp_id' => null,
                'tshirt_size' => sanitize($input['tshirt_size']),
                'session' => sanitize($input['session']),
                'amount' => $courseFee,
                'payment_status' => $paymentStatus,
            ]);

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

            createActivityLog($this->pdo, 'New student registered');

            $this->pdo->commit();

            return [
                'success' => true,
                'registration_number' => $studentRegistrationNo,
                'invoice_number' => $invoiceNumber,
                'student_id' => $studentId,
                'invoice_id' => $invoiceId,
                'payment_status' => $paymentStatus,
                'redirect' => 'invoice.php?id=' . $invoiceId,
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
