INSERT INTO users 
(username, password, authorized, fname, mname, lname, facility_id, active, calendar, taxonomy, npi, info, see_auth)
VALUES
('jdoe', SHA2('ChangeMe123!', 256), 1, 'Jane', 'Marie', 'Doe', 3, 1, 1, '101YP2500X', '1234567890', 'LPC, Licensed Professional Counselor', 1),
('rsmith', SHA2('ChangeMe123!', 256), 1, 'Robert', 'James', 'Smith', 3, 1, 1, '106H00000X', '1234567891', 'LMFT, Licensed Marriage and Family Therapist', 1),
('mgarcia', SHA2('ChangeMe123!', 256), 1, 'Maria', 'Elena', 'Garcia', 3, 1, 1, '103T00000X', '1234567892', 'PhD, Clinical Psychologist', 1),
('dwilson', SHA2('ChangeMe123!', 256), 1, 'David', 'Alan', 'Wilson', 3, 1, 1, '101YP2500X', '1234567893', 'LPC, NCC, Professional Counselor', 1);
