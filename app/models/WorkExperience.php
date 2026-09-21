<?php
class WorkExperience extends BaseModel
{
    protected string $table = 'work_experiences';

    /**
     * Lấy kinh nghiệm làm việc trước đây theo ID nhân viên
     *
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee(int $employeeId): array
    {
        return $this->where(['employee_id' => $employeeId], 'start_date DESC');
    }
}
