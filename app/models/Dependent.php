<?php
class Dependent extends BaseModel
{
    protected string $table = 'dependents';

    /**
     * Lấy danh sách người phụ thuộc theo ID nhân viên
     *
     * @param int $employeeId
     * @return array
     */
    public function getByEmployee(int $employeeId): array
    {
        return $this->where(['employee_id' => $employeeId], 'created_at DESC');
    }
}
