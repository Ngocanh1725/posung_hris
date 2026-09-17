import subprocess
from datetime import date, timedelta
import random

# List of employees
employees = [1, 2, 3, 4, 5, 6, 7, 8, 9]

# Dates in Sep 2026
start_date = date(2026, 9, 1)
end_date = date(2026, 9, 30)

sql_statements = ["USE posung_hris;", 
                  "DELETE FROM timesheets WHERE work_date >= '2026-09-01' AND work_date <= '2026-09-30';",
                  "DELETE FROM payrolls WHERE month = 9 AND year = 2026;"]

current_date = start_date
while current_date <= end_date:
    is_sunday = current_date.weekday() == 6
    for emp_id in employees:
        # Determine project ID (1, 2, 3)
        project_id = (emp_id % 3) + 1
        
        # Decide if they worked
        if is_sunday:
            worked = random.random() < 0.2 # 20% chance to work on Sunday
            shift_type = 'Sunday' if worked else 'Day'
            status = 'Approved' if worked else 'Pending'
        else:
            worked = random.random() < 0.95 # 95% chance to work on weekday
            shift_type = random.choice(['Day', 'Day', 'Day', 'Day', 'Night']) if worked else 'Day'
            status = 'Approved'
            
        if not worked:
            continue
            
        check_in = "08:00:00" if shift_type != 'Night' else "20:00:00"
        check_out = "17:00:00" if shift_type != 'Night' else "05:00:00"
        is_cleanroom = 1 if project_id == 2 and random.random() < 0.5 else 0
        ot_hours = random.choice([0, 0, 0, 1.5, 2.0, 2.5, 3.0])
        
        sql = f"INSERT INTO timesheets (employee_id, project_id, work_date, check_in, check_out, shift_type, is_cleanroom, ot_hours, status) "
        sql += f"VALUES ({emp_id}, {project_id}, '{current_date.strftime('%Y-%m-%d')}', '{check_in}', '{check_out}', '{shift_type}', {is_cleanroom}, {ot_hours}, '{status}');"
        sql_statements.append(sql)
        
    current_date += timedelta(days=1)

# Generate Payrolls
for emp_id in employees:
    project_id = (emp_id % 3) + 1
    actual_days = random.randint(22, 27)
    ot_pay = random.randint(500000, 2000000)
    allowances = random.randint(300000, 1000000)
    deductions = random.randint(0, 200000)
    net_salary = actual_days * 300000 + ot_pay + allowances - deductions
    
    sql = f"INSERT INTO payrolls (month, year, employee_id, project_id, cost_center_id, standard_days, actual_days, ot_pay, allowances_total, deductions_total, net_salary, payment_status) "
    sql += f"VALUES (9, 2026, {emp_id}, {project_id}, NULL, 26.0, {actual_days}, {ot_pay}, {allowances}, {deductions}, {net_salary}, 'Calculated');"
    sql_statements.append(sql)

with open('mock_data.sql', 'w', encoding='utf-8') as f:
    f.write("\n".join(sql_statements))

print("Created mock_data.sql. Running mysql...")
result = subprocess.run(['C:\\xampp\\mysql\\bin\\mysql.exe', '-u', 'root'], input="\n".join(sql_statements).encode('utf-8'))
if result.returncode == 0:
    print("Successfully inserted mock data!")
else:
    print("Error inserting mock data.")
