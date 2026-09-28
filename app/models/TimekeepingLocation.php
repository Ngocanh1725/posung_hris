<?php
/**
 * POSUNG HRIS - TimekeepingLocation Model
 * Quản lý tọa độ GPS và thiết bị điểm danh FaceID
 */

class TimekeepingLocation extends Model
{
    protected $table = 'timekeeping_locations';

    public function getAllLocations()
    {
        $this->db->query("SELECT tl.*, p.project_name, p.project_code 
                          FROM {$this->table} tl 
                          LEFT JOIN projects p ON tl.project_id = p.id
                          ORDER BY tl.id DESC");
        return $this->db->fetchAll();
    }

    public function getLocationById($id)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    public function getLocationsByProject($projectId)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE project_id = :pid AND status = 'Active'");
        $this->db->bind(':pid', $projectId);
        return $this->db->fetchAll();
    }

    public function createLocation($data)
    {
        $this->db->query("INSERT INTO {$this->table} 
            (project_id, location_name, address, latitude, longitude, allowed_radius_meters, device_ip, device_serial, type, status) 
            VALUES (:pid, :ln, :addr, :lat, :lng, :rad, :ip, :ser, :type, :stat)");
        $this->db->bind(':pid', empty($data['project_id']) ? null : $data['project_id']);
        $this->db->bind(':ln', $data['location_name']);
        $this->db->bind(':addr', $data['address'] ?? null);
        $this->db->bind(':lat', $data['latitude'] ?? null);
        $this->db->bind(':lng', $data['longitude'] ?? null);
        $this->db->bind(':rad', $data['allowed_radius_meters'] ?? 100);
        $this->db->bind(':ip', $data['device_ip'] ?? null);
        $this->db->bind(':ser', $data['device_serial'] ?? null);
        $this->db->bind(':type', $data['type'] ?? 'SITE_GPS');
        $this->db->bind(':stat', $data['status'] ?? 'Active');
        return $this->db->execute();
    }

    public function updateLocation($id, $data)
    {
        $this->db->query("UPDATE {$this->table} SET 
            project_id = :pid, location_name = :ln, address = :addr, latitude = :lat, longitude = :lng, 
            allowed_radius_meters = :rad, device_ip = :ip, device_serial = :ser, type = :type, status = :stat 
            WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':pid', empty($data['project_id']) ? null : $data['project_id']);
        $this->db->bind(':ln', $data['location_name']);
        $this->db->bind(':addr', $data['address'] ?? null);
        $this->db->bind(':lat', $data['latitude'] ?? null);
        $this->db->bind(':lng', $data['longitude'] ?? null);
        $this->db->bind(':rad', $data['allowed_radius_meters'] ?? 100);
        $this->db->bind(':ip', $data['device_ip'] ?? null);
        $this->db->bind(':ser', $data['device_serial'] ?? null);
        $this->db->bind(':type', $data['type'] ?? 'SITE_GPS');
        $this->db->bind(':stat', $data['status'] ?? 'Active');
        return $this->db->execute();
    }

    public function deleteLocation($id)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
