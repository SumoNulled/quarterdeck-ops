# QuarterdeckOps

**QuarterdeckOps** is a dynamic system for generating and managing watchbills, handling watch assignments, time slot management, sailor qualifications, and duty location monitoring based on specific constraints. The goal is to ensure fair, efficient, and compliant watch assignments for sailors on both weekdays and weekends, eliminating conflicts with class times, mandatory study periods, and other sailor-specific limitations.

*QuarterdeckOps is a **completely** local solution. Data is stored locally and no internet connection is required, however internet OR intranet will be required to sync data across multiple devices.*
\
\
![image](https://github.com/user-attachments/assets/7ad59993-8ec9-4b99-88d4-ed81769c8e57)

## Project Overview

QuarterdeckOps builds watchbills dynamically by considering qualifications, duty statuses, scheduling conflicts, and building security levels, streamlining the creation of error-free watch schedules while meeting operational needs. The project includes:

- **Dynamic Time Slot Management**: Differentiates between weekday and weekend time slots, updating assignments based on building responsibilities.
- **Sailor Qualifications Filtering**: Assigns watches based on sailor qualifications for secured/unsecured buildings.
- **Automated Conflict Resolution**: Ensures no watch assignments overlap with sailor class times or mandatory study periods.
- **Flexible Duty Assignments**: Supports multiple buildings, with watchstanders prevented from being assigned to duplicate time slots across different buildings on the same day.

## Features

### Key Functionalities

1. **Watchbill Generation**:
   - Assigns watchstanders to designated time slots.
   - Handles double watches if required, ensuring no time slot is left unfilled.

2. **Constraint-Based Scheduling**:
   - **Duty Driver Exclusion**: Duty Drivers do not stand watch.
   - **Leave/Duty Day-Off**: Allows you to mark a sailor as on leave, or having the duty day off, which removes them from the currenty duty day's watch pool.
   - **Limited Duty Restrictions**: Limited Duty personnel can only stand BAW (Building Access Watch) but not BRW (Building Roving Watch).
   - **Security-Based Watch Assignments**: Only qualified sailors can be assigned to SECURED or UNSECURED buildings as per their `basic` and `secure` qualifications.
   - **Class Time and Study Conflict Avoidance**: Filters out sailors with conflicts due to overlapping class schedules or mandatory study times.
   - **Multiple Watch Relief**: A sailor cannot stand two consecutive watches, and no more than 6 hours of duty a day.

3. **Database-Driven Time Slot Assignments**:
   - Detailed schemas for `duty_watchbill`, `sailors`, `duty_locations`, `duty_positions`, `duty_timeslots`, `duty_timeslot_assignments`, and `sailors_class_hours` tables.
   - **Efficient Data Querying** using MySQLi prepared statements to ensure safe and secure database transactions.

4. **User Interface (UI)**:
   - Dropdowns for sailor selections, previewing sailor names by ID.
   - Side-by-side tables for dual building management (Buildings 492 and 534) for easy monitoring.
   - Table is completely interactive, allowing you to manage signed watches and watchstanders.

5. **Automated Status Updates**:
   - Toggling signed statuses with real-time alerts for missing watchstanders.

6. **Manual Watchbill Management**:
   - If a fine-toothed comb is required to complete a watchbill, manual selections are available by clicking on any watch slot to reveal the drop down menu.
   - ![image](https://github.com/user-attachments/assets/5a54273b-2e04-4305-8e91-589adabfcd05)

7. **Clerical Error Handling**:
   - The system prevents the user from making simple mistakes, such as signing for watches that have no one assigned to them.
   - ![image](https://github.com/user-attachments/assets/0bac19ff-c8d6-40ba-85b2-57d3fc447fa3)



## Database Schema

| Table                  | Description                                                                                 |
|------------------------|---------------------------------------------------------------------------------------------|
| `duty_locations`       | Stores building details and classifications (`UNSECURED` or `SECURED`).                     |
| `duty_positions`       | Defines positions for watchstanders, including `BAW`, `BRW`, and `Colors`.                 |
| `duty_timeslots`       | Time slot definitions for start and end times.                                              |
| `duty_timeslot_assignments` | Maps time slots to specific types (`WD`, `WE`, `OTH`).                             |
| `duty_watchbill`       | Tracks watch assignments with foreign keys to `sailors`, `duty_locations`, and time slots. |
| `sailors`              | Holds sailor information, including qualifications and limitations.                        |
| `sailors_class_hours`  | Stores class hours with start and end times, linked to sailors.                            |

# What does it solve?

QDOPS (QuarterdeckOps) solves the complex problem of generating and managing watchbills in a structured, fair, and efficient way. It addresses the challenge of scheduling duty assignments while adhering to strict criteria. By automating certain aspects of watchbill generation, QDOPS not only saves time but also enhances compliance, fairness, and reliability, addressing key operational and personnel concerns.
