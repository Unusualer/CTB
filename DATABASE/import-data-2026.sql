-- ============================================
-- Import Data from CSV - Year 2026
-- Generated: 2026-01-07 12:45:51
-- ============================================

USE ctb_db;

SET FOREIGN_KEY_CHECKS = 0;


-- User: Khadija Abou Maria
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(101, 'user101@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Khadija Abou Maria', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1001, 'apartment', 'A11', 101) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1001, 2026, 3644.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bargach Fatima
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(102, 'user102@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bargach Fatima', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1002, 'apartment', 'A12', 102) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1002, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Adjar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(103, 'user103@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Adjar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1003, 'apartment', 'A13', 103) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1003, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P146 (for A13)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1004, 'parking', 'P146', 103) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P146
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1004, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Amine Bennis
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(104, 'user104@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Amine Bennis', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1005, 'apartment', 'A14', 104) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1005, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Errifai Mustapha
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(105, 'user105@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Errifai Mustapha', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A15
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1006, 'apartment', 'A15', 105) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A15
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1006, 2026, 5466.6) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: AIT BRAHIM
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(106, 'user106@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'AIT BRAHIM', '0605272831', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A16
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1007, 'apartment', 'A16', 106) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A16
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1007, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P224 (for A16)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1008, 'parking', 'P224', 106) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P224
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1008, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aziz Touzani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(107, 'user107@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aziz Touzani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A17
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1009, 'apartment', 'A17', 107) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A17
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1009, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Anass boucheri et abdassalm
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(108, 'user108@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Anass boucheri et abdassalm', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A18
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1010, 'apartment', 'A18', 108) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A18
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1010, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P191 (for A18)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1011, 'parking', 'P191', 108) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P191
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1011, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Andreu Noes Serras
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(109, 'user109@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Andreu Noes Serras', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A19
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1012, 'apartment', 'A19', 109) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A19
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1012, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mariem Aziman
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(110, 'user110@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mariem Aziman', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1013, 'apartment', 'A21', 110) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1013, 2026, 3644.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Achalhi EL Hassan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(111, 'user111@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Achalhi EL Hassan', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1014, 'apartment', 'A22', 111) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1014, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abriak Nor-Edine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(112, 'user112@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abriak Nor-Edine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1015, 'apartment', 'A23', 112) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1015, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Said afia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(113, 'user113@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Said afia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A24
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1016, 'apartment', 'A24', 113) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1016, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P229 (for A24)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1017, 'parking', 'P229', 113) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P229
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1017, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Dakak Halima
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(114, 'user114@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Dakak Halima', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A25
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1018, 'apartment', 'A25', 114) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A25
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1018, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bouras Victor Hamid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(115, 'user115@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bouras Victor Hamid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A26
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1019, 'apartment', 'A26', 115) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A26
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1019, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P206 (for A26)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1020, 'parking', 'P206', 115) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P206
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1020, 2026, 2224) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: MOHAMED EL REGADI
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(116, 'user116@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'MOHAMED EL REGADI', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A27
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1021, 'apartment', 'A27', 116) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A27
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1021, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P231 (for A27)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1022, 'parking', 'P231', 116) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P231
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1022, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohammed Bouyahyaoui et Rabia Lamrhari
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(117, 'user117@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohammed Bouyahyaoui et Rabia Lamrhari', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A28
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1023, 'apartment', 'A28', 117) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A28
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1023, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bouissef Rekab Abdeslam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(118, 'user118@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bouissef Rekab Abdeslam', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A29
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1024, 'apartment', 'A29', 118) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A29
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1024, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mourad bouslham et sanae samit
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(119, 'user119@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mourad bouslham et sanae samit', '0033645842291', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A31
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1025, 'apartment', 'A31', 119) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A31
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1025, 2026, 3644.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Nouha Guennoun Hassani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(120, 'nouhaguennoun@gmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Nouha Guennoun Hassani', '0666435736', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A32
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1026, 'apartment', 'A32', 120) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A32
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1026, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Nadia ammari /Sbai Abdelali
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(121, 'user121@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Nadia ammari /Sbai Abdelali', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A33
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1027, 'apartment', 'A33', 121) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A33
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1027, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bilal Achab
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(122, 'user122@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bilal Achab', '0620556114', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A34
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1028, 'apartment', 'A34', 122) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A34
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1028, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: El bakkalli Samira
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(123, 'user123@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'El bakkalli Samira', '0661571756', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A35
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1029, 'apartment', 'A35', 123) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A35
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1029, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P163 (for A35)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1030, 'parking', 'P163', 123) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P163
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1030, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Nabil touria  Gheiel
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(124, 'user124@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Nabil touria  Gheiel', '0661990321', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A36
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1031, 'apartment', 'A36', 124) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A36
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1031, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Najib Cheraibi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(125, 'user125@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Najib Cheraibi', '0661144981', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A37
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1032, 'apartment', 'A37', 125) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A37
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1032, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Fouhad Auderam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(126, 'user126@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Fouhad Auderam', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A38
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1033, 'apartment', 'A38', 126) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A38
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1033, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Charia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(127, 'user127@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Charia', '0661362616', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A39
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1034, 'apartment', 'A39', 127) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A39
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1034, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: MR SAHIB HASOUN SHLASH
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(128, 'user128@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'MR SAHIB HASOUN SHLASH', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A41
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1035, 'apartment', 'A41', 128) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A41
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1035, 2026, 3644.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Harras Mariam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(129, 'user129@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Harras Mariam', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A42
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1036, 'apartment', 'A42', 129) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A42
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1036, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: docteur Bellamine Mly Smail
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(130, 'user130@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'docteur Bellamine Mly Smail', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A43
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1037, 'apartment', 'A43', 130) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A43
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1037, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ziaulhaq Shaikh
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(131, 'user131@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ziaulhaq Shaikh', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A44
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1038, 'apartment', 'A44', 131) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A44
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1038, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Baraky Ahmed  (hamida SENBAK)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(132, 'user132@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Baraky Ahmed  (hamida SENBAK)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A45
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1039, 'apartment', 'A45', 132) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A45
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1039, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Group Skosia SARL
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(133, 'user133@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Group Skosia SARL', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A46
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1040, 'apartment', 'A46', 133) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A46
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1040, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Omar Dawood Mapara
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(134, 'user134@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Omar Dawood Mapara', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A47
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1041, 'apartment', 'A47', 134) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A47
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1041, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Azhari Yacine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(135, 'yasa2har@hotmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Azhari Yacine', '0666279161', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A48
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1042, 'apartment', 'A48', 135) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A48
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1042, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P232 (for A48)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1043, 'parking', 'P232', 135) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P232
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1043, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: hsaien Abedrrazzak et mme dahamn touria
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(136, 'user136@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'hsaien Abedrrazzak et mme dahamn touria', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A49
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1044, 'apartment', 'A49', 136) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A49
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1044, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Hassane Omari Tadlaoui
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(137, 'user137@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Hassane Omari Tadlaoui', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A51
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1045, 'apartment', 'A51', 137) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A51
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1045, 2026, 3644.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mahsoune M\'Barek
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(138, 'user138@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mahsoune M\'Barek', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A52
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1046, 'apartment', 'A52', 138) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A52
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1046, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Fernando Lama Rodriguez
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(139, 'user139@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Fernando Lama Rodriguez', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A53
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1047, 'apartment', 'A53', 139) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A53
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1047, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ennahrawani Khaddoj
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(140, 'user140@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ennahrawani Khaddoj', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A54
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1048, 'apartment', 'A54', 140) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A54
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1048, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abderrahman Boulhar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(141, 'user141@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abderrahman Boulhar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A55
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1049, 'apartment', 'A55', 141) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A55
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1049, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P59 (for A55)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1050, 'parking', 'P59', 141) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P59
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1050, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: PBox (for A55)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1051, 'parking', 'PBox', 141) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking PBox
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1051, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Yacub Caratella
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(142, 'user142@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Yacub Caratella', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A56
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1052, 'apartment', 'A56', 142) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A56
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1052, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Botte Hamid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(143, 'user143@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Botte Hamid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A57
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1053, 'apartment', 'A57', 143) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A57
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1053, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P227 (for A57)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1054, 'parking', 'P227', 143) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P227
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1054, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohammed Redouan Metni
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(144, 'user144@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohammed Redouan Metni', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A58
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1055, 'apartment', 'A58', 144) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A58
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1055, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P213 (for A58)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1056, 'parking', 'P213', 144) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P213
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1056, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Proubi Abdeslam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(145, 'user145@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Proubi Abdeslam', '0661297176', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A59
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1057, 'apartment', 'A59', 145) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A59
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1057, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P149 (for A59)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1058, 'parking', 'P149', 145) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P149
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1058, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Hanan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(146, 'user146@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Hanan', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A61
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1059, 'apartment', 'A61', 146) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A61
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1059, 2026, 3644.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Khadija EL maimouni
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(147, 'user147@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Khadija EL maimouni', '003366322939', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A62
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1060, 'apartment', 'A62', 147) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A62
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1060, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sabrina
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(148, 'user148@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sabrina', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A63
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1061, 'apartment', 'A63', 148) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A63
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1061, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Latifa Khayroune
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(149, 'user149@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Latifa Khayroune', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A64
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1062, 'apartment', 'A64', 149) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A64
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1062, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Alouza Zakia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(150, 'user150@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Alouza Zakia', '0661477191', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A65
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1063, 'apartment', 'A65', 150) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A65
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1063, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P233 (for A65)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1064, 'parking', 'P233', 150) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P233
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1064, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Lhoussain Benayad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(151, 'user151@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Lhoussain Benayad', '0636130401', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A66
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1065, 'apartment', 'A66', 151) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A66
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1065, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Naima mojoud
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(152, 'user152@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Naima mojoud', '0612920824', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A67
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1066, 'apartment', 'A67', 152) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A67
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1066, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bendada Naoufal
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(153, 'user153@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bendada Naoufal', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A68
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1067, 'apartment', 'A68', 153) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A68
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1067, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Boulaich Anis et karim
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(154, 'user154@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Boulaich Anis et karim', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A69
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1068, 'apartment', 'A69', 154) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A69
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1068, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P151 (for A69)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1069, 'parking', 'P151', 154) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P151
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1069, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Shabnam Valu
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(155, 'user155@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Shabnam Valu', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A71
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1070, 'apartment', 'A71', 155) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A71
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1070, 2026, 3644.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Jamila El azhari
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(156, 'user156@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Jamila El azhari', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A72
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1071, 'apartment', 'A72', 156) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A72
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1071, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: saad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(157, 'user157@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'saad', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A73
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1072, 'apartment', 'A73', 157) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A73
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1072, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bel Yamani Salim
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(158, 'user158@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bel Yamani Salim', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A74
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1073, 'apartment', 'A74', 158) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A74
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1073, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Soraya et Rania Alaoui Mdaghri
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(159, 'user159@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Soraya et Rania Alaoui Mdaghri', '0661096151', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A75
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1074, 'apartment', 'A75', 159) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A75
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1074, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P122 (for A75)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1075, 'parking', 'P122', 159) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P122
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1075, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: samira doukali
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(160, 'user160@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'samira doukali', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A76
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1076, 'apartment', 'A76', 160) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A76
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1076, 2026, 5831.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P216 (for A76)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1077, 'parking', 'P216', 160) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P216
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1077, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: A77
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1078, 'apartment', 'A77', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A77
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1078, 2026, 3279.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P70 (for A77)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1079, 'parking', 'P70', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P70
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1079, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: A78
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1080, 'apartment', 'A78', 133) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A78
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1080, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P134 (for A78)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1081, 'parking', 'P134', 133) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P134
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1081, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Nesh Nesh Ahmed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(161, 'user161@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Nesh Nesh Ahmed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A79
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1082, 'apartment', 'A79', 161) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A79
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1082, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P116 (for A79)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1083, 'parking', 'P116', 161) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P116
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1083, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mme LEBBADI & OMAR LKHATIB
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(162, 'user162@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mme LEBBADI & OMAR LKHATIB', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A81
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1084, 'apartment', 'A81', 162) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A81
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1084, 2026, 5102.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sami ouazafi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(163, 'user163@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sami ouazafi', '0620905099', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A82
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1085, 'apartment', 'A82', 163) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A82
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1085, 2026, 5466.6) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: A83
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1086, 'apartment', 'A83', 104) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A83
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1086, 2026, 4737.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: ouamid (Abou Ali)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(164, 'user164@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'ouamid (Abou Ali)', '0044793227785', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A84
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1087, 'apartment', 'A84', 164) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A84
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1087, 2026, 6559.92) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P133 (for A84)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1088, 'parking', 'P133', 164) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P133
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1088, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: GHARBI SOUHAIL
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(165, 'user165@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'GHARBI SOUHAIL', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A85
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1089, 'apartment', 'A85', 165) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A85
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1089, 2026, 6924.36) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Rachid Amhama
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(166, 'user166@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Rachid Amhama', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A86
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1090, 'apartment', 'A86', 166) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A86
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1090, 2026, 6924.36) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P167 (for A86)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1091, 'parking', 'P167', 166) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P167
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1091, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Vicente Javier Ramirez
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(167, 'user167@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Vicente Javier Ramirez', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A87
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1092, 'apartment', 'A87', 167) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A87
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1092, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P179 (for A87)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1093, 'parking', 'P179', 167) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P179
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1093, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: farid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(168, 'user168@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'farid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A91
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1094, 'apartment', 'A91', 168) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A91
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1094, 2026, 11662.08) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: A92
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1095, 'apartment', 'A92', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A92
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1095, 2026, 7653.24) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: ELYASS Benchabatt
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(169, 'user169@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'ELYASS Benchabatt', '0033626141234', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A93
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1096, 'apartment', 'A93', 169) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A93
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1096, 2026, 9839.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Manuel Ignacio Mora Roche
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(170, 'user170@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Manuel Ignacio Mora Roche', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: A94
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1097, 'apartment', 'A94', 170) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A94
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1097, 2026, 9839.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P203 (for A94)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1098, 'parking', 'P203', 170) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P203
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1098, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P211 (for A94)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1099, 'parking', 'P211', 170) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P211
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1099, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: A95
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1100, 'apartment', 'A95', 170) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A95
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1100, 2026, 8017.68) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: A96
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1101, 'apartment', 'A96', 167) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment A96
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1101, 2026, 9839.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Magdalena Roldan Romero
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(171, 'user171@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Magdalena Roldan Romero', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/1
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1102, 'apartment', 'B1/1', 171) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/1
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1102, 2026, 3619.19) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P111 (for B1/1)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1103, 'parking', 'P111', 171) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P111
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1103, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bouamir Bachir et Véronique  (Abdelah son frére)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(172, 'user172@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bouamir Bachir et Véronique  (Abdelah son frére)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/2
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1104, 'apartment', 'B1/2', 172) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/2
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1104, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: karim Rguiouag
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(173, 'user173@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'karim Rguiouag', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/3
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1105, 'apartment', 'B1/3', 173) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/3
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1105, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P97 (for B1/3)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1106, 'parking', 'P97', 173) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P97
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1106, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Farid Hdioud
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(174, 'user174@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Farid Hdioud', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/4
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1107, 'apartment', 'B1/4', 174) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/4
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1107, 2026, 3257.27) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P96 (for B1/4)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1108, 'parking', 'P96', 174) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P96
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1108, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abdelhakim Hemdaoui
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(175, 'user175@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abdelhakim Hemdaoui', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/5
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1109, 'apartment', 'B1/5', 175) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/5
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1109, 2026, 6876.46) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Gnaou Dounia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(176, 'user176@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Gnaou Dounia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/6
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1110, 'apartment', 'B1/6', 176) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/6
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1110, 2026, 6876.46) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Rachid Agueznay
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(177, 'user177@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Rachid Agueznay', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/7
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1111, 'apartment', 'B1/7', 177) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/7
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1111, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P230 (for B1/7)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1112, 'parking', 'P230', 177) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P230
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1112, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Berrada Arii
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(178, 'user178@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Berrada Arii', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1113, 'apartment', 'B1/11', 178) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1113, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P109 (for B1/11)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1114, 'parking', 'P109', 178) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P109
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1114, 2026, 4310) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aboubakr
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(179, 'user179@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aboubakr', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1115, 'apartment', 'B1/12', 179) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1115, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P64 (for B1/12)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1116, 'parking', 'P64', 179) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P64
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1116, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Nuzha Amiar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(180, 'user180@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Nuzha Amiar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1117, 'apartment', 'B1/13', 180) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1117, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P40 (for B1/13)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1118, 'parking', 'P40', 180) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P40
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1118, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed mjahed Mansouri
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(181, 'mansourimjahd@gmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed mjahed Mansouri', '00330652910259', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1119, 'apartment', 'B1/14', 181) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1119, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P77 (for B1/14)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1120, 'parking', 'P77', 181) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P77
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1120, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Boumediane EL Hassan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(182, 'user182@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Boumediane EL Hassan', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/15
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1121, 'apartment', 'B1/15', 182) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/15
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1121, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Alaoui Mdaghri Mostafa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(183, 'user183@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Alaoui Mdaghri Mostafa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/16
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1122, 'apartment', 'B1/16', 183) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/16
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1122, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P158 (for B1/16)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1123, 'parking', 'P158', 183) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P158
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1123, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Yazaji Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(184, 'user184@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Yazaji Mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1124, 'apartment', 'B1/21', 184) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1124, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ghizlan Chergui
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(185, 'user185@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ghizlan Chergui', '00503', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1125, 'apartment', 'B1/22', 185) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1125, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Namae El Bakkali ET Moughit Sahnoun
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(186, 'moghit10@hotmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Namae El Bakkali ET Moughit Sahnoun', '00212613783163', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1126, 'apartment', 'B1/23', 186) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1126, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abdelkader Boukourna
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(187, 'user187@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abdelkader Boukourna', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/24
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1127, 'apartment', 'B1/24', 187) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1127, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Ammari Nabil
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(188, 'user188@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Ammari Nabil', '+212663010941', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/25
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1128, 'apartment', 'B1/25', 188) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/25
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1128, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P148 (for B1/25)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1129, 'parking', 'P148', 188) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P148
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1129, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B1/26
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1130, 'apartment', 'B1/26', 146) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/26
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1130, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P201 (for B1/26)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1131, 'parking', 'P201', 146) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P201
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1131, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Soussi Jaafar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(189, 'user189@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Soussi Jaafar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/31
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1132, 'apartment', 'B1/31', 189) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/31
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1132, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P137 (for B1/31)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1133, 'parking', 'P137', 189) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P137
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1133, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Zinati Latifa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(190, 'user190@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Zinati Latifa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/32
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1134, 'apartment', 'B1/32', 190) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/32
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1134, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Amhaji Lahcen
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(191, 'user191@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Amhaji Lahcen', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/34
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1135, 'apartment', 'B1/34', 191) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/34
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1135, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P177 (for B1/34)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1136, 'parking', 'P177', 191) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P177
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1136, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Lanjri Khalid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(192, 'user192@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Lanjri Khalid', '0656731649', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/35
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1137, 'apartment', 'B1/35', 192) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/35
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1137, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P152 (for B1/35)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1138, 'parking', 'P152', 192) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P152
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1138, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mehdi ibn abdelouahab
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(193, 'user193@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mehdi ibn abdelouahab', '0661587746', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/36
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1139, 'apartment', 'B1/36', 193) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/36
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1139, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P222 (for B1/36)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1140, 'parking', 'P222', 193) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P222
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1140, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: bahia gharbi / abedfatah ziani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(194, 'user194@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'bahia gharbi / abedfatah ziani', '0672118549', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/41
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1141, 'apartment', 'B1/41', 194) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/41
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1141, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P198 (for B1/41)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1142, 'parking', 'P198', 194) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P198
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1142, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Benmlih Mariem
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(195, 'user195@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Benmlih Mariem', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/42
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1143, 'apartment', 'B1/42', 195) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/42
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1143, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Edouard sebag
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(196, '733canada@gmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Edouard sebag', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/44
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1144, 'apartment', 'B1/44', 196) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/44
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1144, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Chantouf Amine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(197, 'user197@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Chantouf Amine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/45
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1145, 'apartment', 'B1/45', 197) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/45
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1145, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mohamed khazrouni
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(198, 'user198@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mohamed khazrouni', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/46
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1146, 'apartment', 'B1/46', 198) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/46
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1146, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Taibi Najib
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(199, 'user199@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Taibi Najib', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/51
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1147, 'apartment', 'B1/51', 199) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/51
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1147, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P114 (for B1/51)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1148, 'parking', 'P114', 199) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P114
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1148, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ahmed rouani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(200, 'user200@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ahmed rouani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/52
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1149, 'apartment', 'B1/52', 200) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/52
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1149, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Amjad LASRI
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(201, 'user201@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Amjad LASRI', '+33767538652', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/53
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1150, 'apartment', 'B1/53', 201) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/53
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1150, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abdelfettah Errarhay et Rachida
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(202, 'user202@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abdelfettah Errarhay et Rachida', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/54
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1151, 'apartment', 'B1/54', 202) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/54
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1151, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: FOUAD ABDOUNE
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(203, 'user203@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'FOUAD ABDOUNE', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/55
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1152, 'apartment', 'B1/55', 203) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/55
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1152, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Casado Rigalt Daniel ET Antonio
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(204, 'user204@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Casado Rigalt Daniel ET Antonio', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/56
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1153, 'apartment', 'B1/56', 204) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/56
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1153, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P113 (for B1/56)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1154, 'parking', 'P113', 204) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P113
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1154, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B1/61
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1155, 'apartment', 'B1/61', 154) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/61
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1155, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Baali Youssef
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(205, 'user205@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Baali Youssef', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/62
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1156, 'apartment', 'B1/62', 205) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/62
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1156, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: laila cherfaoui
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(206, 'user206@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'laila cherfaoui', '00330698709411', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/64
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1157, 'apartment', 'B1/64', 206) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/64
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1157, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Allali Hakim
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(207, 'user207@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Allali Hakim', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/65
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1158, 'apartment', 'B1/65', 207) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/65
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1158, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P78 (for B1/65)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1159, 'parking', 'P78', 207) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P78
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1159, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Maarouf Redouane
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(208, 'user208@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Maarouf Redouane', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/66
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1160, 'apartment', 'B1/66', 208) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/66
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1160, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ferdaws El iamani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(209, 'ferdawsel@hotmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ferdaws El iamani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/71
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1161, 'apartment', 'B1/71', 209) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/71
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1161, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Bouazzaoui Khalid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(210, 'user210@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Bouazzaoui Khalid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/72
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1162, 'apartment', 'B1/72', 210) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/72
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1162, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: hassan el mokkadam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(211, 'user211@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'hassan el mokkadam', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/74
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1163, 'apartment', 'B1/74', 211) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/74
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1163, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ubaretxena jauan et Manuel
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(212, 'user212@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ubaretxena jauan et Manuel', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/76
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1164, 'apartment', 'B1/76', 212) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/76
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1164, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P66 (for B1/76)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1165, 'parking', 'P66', 212) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P66
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1165, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: NADIA BEN FQUIN
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(213, 'user213@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'NADIA BEN FQUIN', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/81
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1166, 'apartment', 'B1/81', 213) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/81
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1166, 2026, 13029.08) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P112 (for B1/81)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1167, 'parking', 'P112', 213) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P112
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1167, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mestapha EL Younssi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(214, 'user214@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mestapha EL Younssi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/82
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1168, 'apartment', 'B1/82', 214) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/82
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1168, 2026, 7238.38) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bennani Omar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(215, 'user215@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bennani Omar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/83
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1169, 'apartment', 'B1/83', 215) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/83
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1169, 2026, 11581.41) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: ADIL
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(216, 'user216@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'ADIL', '0778033025', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/84
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1170, 'apartment', 'B1/84', 216) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/84
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1170, 2026, 17010.19) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: theophile ou chevalier
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(217, 'user217@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'theophile ou chevalier', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B1/85
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1171, 'apartment', 'B1/85', 217) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B1/85
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1171, 2026, 9409.89) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Gnaou Nour-eddine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(218, 'user218@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Gnaou Nour-eddine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/1
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1172, 'apartment', 'B2/1', 218) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/1
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1172, 2026, 6152.62) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P99 (for B2/1)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1173, 'parking', 'P99', 218) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P99
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1173, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Gnaou Soraya
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(219, 'user219@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Gnaou Soraya', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/3
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1174, 'apartment', 'B2/3', 219) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/3
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1174, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P154 (for B2/3)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1175, 'parking', 'P154', 219) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P154
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1175, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B2/6
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1176, 'apartment', 'B2/6', 179) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/6
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1176, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Ajana
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(220, 'user220@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Ajana', '0660474019', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1177, 'apartment', 'B2/11', 220) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1177, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P63 (for B2/11)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1178, 'parking', 'P63', 220) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P63
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1178, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Driss zekri
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(221, 'user221@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Driss zekri', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1179, 'apartment', 'B2/13', 221) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1179, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P51 (for B2/13)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1180, 'parking', 'P51', 221) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P51
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1180, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: iman draou
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(222, 'user222@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'iman draou', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1181, 'apartment', 'B2/14', 222) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1181, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P45 (for B2/14)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1182, 'parking', 'P45', 222) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P45
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1182, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Larissi Mohamed ET Naima
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(223, 'user223@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Larissi Mohamed ET Naima', '0661485676', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1183, 'apartment', 'B2/21', 223) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1183, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P88 (for B2/21)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1184, 'parking', 'P88', 223) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P88
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1184, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mfarraj khadija
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(224, 'user224@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mfarraj khadija', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1185, 'apartment', 'B2/22', 224) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1185, 2026, 6152.62) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P74 (for B2/22)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1186, 'parking', 'P74', 224) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P74
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1186, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Karrouk Sanaa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(225, 'user225@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Karrouk Sanaa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1187, 'apartment', 'B2/23', 225) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1187, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P21 (for B2/23)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1188, 'parking', 'P21', 225) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1188, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Loukili Abdelilah
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(226, 'albertootreklo645@gmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Loukili Abdelilah', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/24
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1189, 'apartment', 'B2/24', 226) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1189, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P46 (for B2/24)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1190, 'parking', 'P46', 226) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P46
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1190, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ziani Abdelhakim
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(227, 'user227@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ziani Abdelhakim', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/31
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1191, 'apartment', 'B2/31', 227) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/31
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1191, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Yaakoubi safia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(228, 'user228@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Yaakoubi safia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/34
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1192, 'apartment', 'B2/34', 228) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/34
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1192, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P52 (for B2/34)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1193, 'parking', 'P52', 228) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P52
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1193, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Yassini Imad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(229, 'user229@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Yassini Imad', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/41
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1194, 'apartment', 'B2/41', 229) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/41
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1194, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mohamed jamai
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(230, 'user230@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mohamed jamai', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/42
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1195, 'apartment', 'B2/42', 230) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/42
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1195, 2026, 6152.62) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abdeilah tribak
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(231, 'user231@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abdeilah tribak', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/43
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1196, 'apartment', 'B2/43', 231) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/43
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1196, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P236 (for B2/43)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1197, 'parking', 'P236', 231) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P236
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1197, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Saleha Aarabe
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(232, 'user232@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Saleha Aarabe', '+34659644824', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/44
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1198, 'apartment', 'B2/44', 232) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/44
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1198, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P41 (for B2/44)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1199, 'parking', 'P41', 232) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P41
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1199, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Rhamni Khalid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(233, 'user233@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Rhamni Khalid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/51
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1200, 'apartment', 'B2/51', 233) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/51
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1200, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed EL AZZAB
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(234, 'user234@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed EL AZZAB', '+0033172563686', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/52
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1201, 'apartment', 'B2/52', 234) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/52
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1201, 2026, 6152.62) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: salomon Anidjar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(235, 'user235@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'salomon Anidjar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/53
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1202, 'apartment', 'B2/53', 235) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/53
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1202, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P55 (for B2/53)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1203, 'parking', 'P55', 235) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P55
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1203, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: aziz mzerouri
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(236, 'user236@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'aziz mzerouri', '0610777958', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/54
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1204, 'apartment', 'B2/54', 236) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/54
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1204, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aboubaker seffar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(237, 'user237@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aboubaker seffar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/61
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1205, 'apartment', 'B2/61', 237) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/61
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1205, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P31 (for B2/61)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1206, 'parking', 'P31', 237) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P31
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1206, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ghannam Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(238, 'user238@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ghannam Mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/62
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1207, 'apartment', 'B2/62', 238) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/62
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1207, 2026, 6152.62) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P73 (for B2/62)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1208, 'parking', 'P73', 238) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P73
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1208, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: bouchra et layla Benserghi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(239, 'user239@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'bouchra et layla Benserghi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/63
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1209, 'apartment', 'B2/63', 239) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/63
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1209, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P18 (for B2/63)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1210, 'parking', 'P18', 239) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P18
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1210, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Hasnae Mesoudi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(240, 'user240@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Hasnae Mesoudi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/64
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1211, 'apartment', 'B2/64', 240) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/64
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1211, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P17 (for B2/64)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1212, 'parking', 'P17', 240) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P17
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1212, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aboubaker Seffar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(241, 'user241@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aboubaker Seffar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/71
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1213, 'apartment', 'B2/71', 241) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/71
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1213, 2026, 5066.86) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P79 (for B2/71)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1214, 'parking', 'P79', 241) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P79
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1214, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mohamed ey radouane ouanaya
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(242, 'user242@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mohamed ey radouane ouanaya', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/72
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1215, 'apartment', 'B2/72', 242) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/72
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1215, 2026, 6152.62) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P19 (for B2/72)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1216, 'parking', 'P19', 242) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P19
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1216, 2026, 2224) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Charaf Tourya
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(243, 'user243@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Charaf Tourya', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/74
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1217, 'apartment', 'B2/74', 243) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/74
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1217, 2026, 11219.49) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P68 (for B2/74)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1218, 'parking', 'P68', 243) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P68
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1218, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P69 (for B2/74)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1219, 'parking', 'P69', 243) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P69
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1219, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Younssi Mustapha
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(244, 'user244@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Younssi Mustapha', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/81
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1220, 'apartment', 'B2/81', 244) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/81
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1220, 2026, 4704.95) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P24 (for B2/81)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1221, 'parking', 'P24', 244) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1221, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Dekkaki Zakaria
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(245, 'user245@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Dekkaki Zakaria', '+971506303280', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/82
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1222, 'apartment', 'B2/82', 245) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/82
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1222, 2026, 3257.27) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P226 (for B2/82)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1223, 'parking', 'P226', 245) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P226
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1223, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed el ouarghi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(246, 'user246@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed el ouarghi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/83
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1224, 'apartment', 'B2/83', 246) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/83
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1224, 2026, 13752.92) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P181 (for B2/83)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1225, 'parking', 'P181', 246) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P181
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1225, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Amrani Mustapha
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(247, 'user247@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Amrani Mustapha', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B2/84
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1226, 'apartment', 'B2/84', 247) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B2/84
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1226, 2026, 13029.08) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/01
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1227, 'apartment', 'B3/01', 179) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/01
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1227, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/02
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1228, 'apartment', 'B3/02', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/02
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1228, 2026, 6876.46) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/03
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1229, 'apartment', 'B3/03', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/03
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1229, 2026, 7238.38) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/04
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1230, 'apartment', 'B3/04', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/04
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1230, 2026, 8324.14) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: ouafae chabaa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(248, 'user248@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'ouafae chabaa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/05
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1231, 'apartment', 'B3/05', 248) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/05
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1231, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Goelli Fadila (azarkan farida)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(249, 'user249@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Goelli Fadila (azarkan farida)', '0612904320', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/6
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1232, 'apartment', 'B3/6', 249) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/6
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1232, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: lezrek rachid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(250, 'user250@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'lezrek rachid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/7
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1233, 'apartment', 'B3/7', 250) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/7
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1233, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Laarbi Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(251, 'user251@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Laarbi Mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/8
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1234, 'apartment', 'B3/8', 251) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/8
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1234, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Gnaou Nour-Eddine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(252, 'user252@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Gnaou Nour-Eddine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/9
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1235, 'apartment', 'B3/9', 252) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/9
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1235, 2026, 9771.81) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P168 (for B3/9)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1236, 'parking', 'P168', 252) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P168
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1236, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Gnaou Soumaya
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(253, 'user253@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Gnaou Soumaya', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/10
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1237, 'apartment', 'B3/10', 253) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/10
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1237, 2026, 7600.3) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P192 (for B3/10)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1238, 'parking', 'P192', 253) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P192
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1238, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1239, 'apartment', 'B3/11', 146) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1239, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P84 (for B3/11)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1240, 'parking', 'P84', 146) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P84
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1240, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Kerrich Abdelkrim
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(254, 'user254@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Kerrich Abdelkrim', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/11*
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1241, 'apartment', 'B3/11*', 254) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/11*
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1241, 2026, 5428.78) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P157 (for B3/11*)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1242, 'parking', 'P157', 254) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P157
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1242, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mouad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(255, 'user255@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mouad', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1243, 'apartment', 'B3/12', 255) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1243, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P54 (for B3/12)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1244, 'parking', 'P54', 255) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P54
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1244, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: hicham ahrich
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(256, 'user256@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'hicham ahrich', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1245, 'apartment', 'B3/13', 256) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1245, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P144 (for B3/13)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1246, 'parking', 'P144', 256) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P144
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1246, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sebban Omar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(257, 'user257@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sebban Omar', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1247, 'apartment', 'B3/14', 257) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1247, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P104 (for B3/14)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1248, 'parking', 'P104', 257) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P104
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1248, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/15
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1249, 'apartment', 'B3/15', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/15
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1249, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Brahmi Salah
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(258, 'user258@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Brahmi Salah', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/16
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1250, 'apartment', 'B3/16', 258) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/16
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1250, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P103 (for B3/16)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1251, 'parking', 'P103', 258) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P103
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1251, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Rhimou et Mailoudi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(259, 'user259@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Rhimou et Mailoudi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/17
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1252, 'apartment', 'B3/17', 259) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/17
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1252, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Dawod Abbas
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(260, 'user260@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Dawod Abbas', '0623190906', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/18
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1253, 'apartment', 'B3/18', 260) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/18
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1253, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P44 (for B3/18)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1254, 'parking', 'P44', 260) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P44
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1254, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Skir Abdelfettah
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(261, 'user261@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Skir Abdelfettah', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1255, 'apartment', 'B3/21', 261) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1255, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ahmed Rami
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(262, 'user262@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ahmed Rami', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1256, 'apartment', 'B3/22', 262) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1256, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Hamid Bia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(263, 'user263@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Hamid Bia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1257, 'apartment', 'B3/23', 263) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1257, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bojaada El Hassan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(264, 'user264@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bojaada El Hassan', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/24
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1258, 'apartment', 'B3/24', 264) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1258, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Raha Youssef
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(265, 'user265@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Raha Youssef', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/25
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1259, 'apartment', 'B3/25', 265) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/25
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1259, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P39 (for B3/25)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1260, 'parking', 'P39', 265) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P39
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1260, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bouhmadi Yassin
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(266, 'user266@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bouhmadi Yassin', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/26
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1261, 'apartment', 'B3/26', 266) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/26
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1261, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Zinab taleb
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(267, 'user267@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Zinab taleb', '0033603444816', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/27
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1262, 'apartment', 'B3/27', 267) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/27
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1262, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Kerrich sara
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(268, 'user268@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Kerrich sara', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/28
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1263, 'apartment', 'B3/28', 268) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/28
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1263, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P180 (for B3/28)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1264, 'parking', 'P180', 268) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P180
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1264, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Ouazzani Lalla Fouzia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(269, 'user269@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Ouazzani Lalla Fouzia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/31
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1265, 'apartment', 'B3/31', 269) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/31
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1265, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sbai Mohamed et Sbai Tarik
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(270, 'user270@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sbai Mohamed et Sbai Tarik', '0672969619', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/32
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1266, 'apartment', 'B3/32', 270) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/32
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1266, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Omrani et Moller
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(271, 'user271@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Omrani et Moller', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/33
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1267, 'apartment', 'B3/33', 271) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/33
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1267, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ben Cherif Latifa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(272, 'user272@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ben Cherif Latifa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/34
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1268, 'apartment', 'B3/34', 272) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/34
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1268, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mueden Abdelaziz
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(273, 'user273@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mueden Abdelaziz', '0661257424', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/35
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1269, 'apartment', 'B3/35', 273) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/35
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1269, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P117 (for B3/35)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1270, 'parking', 'P117', 273) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P117
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1270, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ramou Abdeslam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(274, 'user274@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ramou Abdeslam', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/36
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1271, 'apartment', 'B3/36', 274) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/36
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1271, 2026, 4343.03) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P57 (for B3/36)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1272, 'parking', 'P57', 274) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P57
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1272, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ramoun Jawad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(275, 'user275@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ramoun Jawad', '0624143203', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/37
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1273, 'apartment', 'B3/37', 275) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/37
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1273, 2026, 3981.11) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/38
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1274, 'apartment', 'B3/38', 254) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/38
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1274, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohammed Tahri
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(276, 'user276@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohammed Tahri', '0667552116', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/41
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1275, 'apartment', 'B3/41', 276) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/41
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1275, 2026, 5790.7) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P56 (for B3/41)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1276, 'parking', 'P56', 276) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P56
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1276, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Larbi Arioua et Rahma El Ouaret
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(277, 'user277@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Larbi Arioua et Rahma El Ouaret', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/42
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1277, 'apartment', 'B3/42', 277) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/42
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1277, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P87 (for B3/42)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1278, 'parking', 'P87', 277) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P87
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1278, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Marie Josette Pineau et Tahiri Laarbi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(278, 'user278@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Marie Josette Pineau et Tahiri Laarbi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/43
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1279, 'apartment', 'B3/43', 278) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/43
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1279, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P139 (for B3/43)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1280, 'parking', 'P139', 278) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P139
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1280, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/44
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1281, 'apartment', 'B3/44', 277) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/44
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1281, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P115 (for B3/44)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1282, 'parking', 'P115', 277) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P115
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1282, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ahmed Mouji
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(279, 'user279@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ahmed Mouji', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/45
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1283, 'apartment', 'B3/45', 279) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/45
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1283, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Dbab Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(280, 'user280@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Dbab Mohamed', '0661159356', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/46
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1284, 'apartment', 'B3/46', 280) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/46
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1284, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: redouan safi / bouchera
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(281, 'user281@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'redouan safi / bouchera', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/47
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1285, 'apartment', 'B3/47', 281) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/47
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1285, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Issaoui aziz
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(282, 'user282@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Issaoui aziz', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/48
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1286, 'apartment', 'B3/48', 282) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/48
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1286, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P86 (for B3/48)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1287, 'parking', 'P86', 282) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P86
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1287, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sermouh Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(283, 'user283@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sermouh Mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/51
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1288, 'apartment', 'B3/51', 283) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/51
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1288, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: ouriach mounir
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(284, 'user284@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'ouriach mounir', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/52
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1289, 'apartment', 'B3/52', 284) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/52
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1289, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P50 (for B3/52)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1290, 'parking', 'P50', 284) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P50
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1290, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Naushad Ali Kajani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(285, 'user285@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Naushad Ali Kajani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/53
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1291, 'apartment', 'B3/53', 285) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/53
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1291, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P135 (for B3/53)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1292, 'parking', 'P135', 285) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P135
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1292, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abdelkader ( Sky New)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(286, 'user286@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abdelkader ( Sky New)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/54
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1293, 'apartment', 'B3/54', 286) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/54
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1293, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P62 (for B3/54)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1294, 'parking', 'P62', 286) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P62
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1294, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ben yussef Ahmed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(287, 'user287@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ben yussef Ahmed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/55
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1295, 'apartment', 'B3/55', 287) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/55
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1295, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P225 (for B3/55)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1296, 'parking', 'P225', 287) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P225
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1296, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mr et Mme Colleu
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(288, 'user288@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mr et Mme Colleu', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/56
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1297, 'apartment', 'B3/56', 288) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/56
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1297, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P172 (for B3/56)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1298, 'parking', 'P172', 288) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P172
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1298, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: HASSAN EL MOKADEM
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(289, 'user289@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'HASSAN EL MOKADEM', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/57
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1299, 'apartment', 'B3/57', 289) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/57
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1299, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P67 (for B3/57)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1300, 'parking', 'P67', 289) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P67
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1300, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Benali Ilham
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(290, 'user290@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Benali Ilham', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/58
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1301, 'apartment', 'B3/58', 290) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/58
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1301, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P110 (for B3/58)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1302, 'parking', 'P110', 290) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P110
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1302, 2026, 2224) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Latifa Alaoui Ismaili
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(291, 'user291@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Latifa Alaoui Ismaili', '0033630857596', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/61
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1303, 'apartment', 'B3/61', 291) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/61
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1303, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abkari Zoubida
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(292, 'user292@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abkari Zoubida', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/62
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1304, 'apartment', 'B3/62', 292) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/62
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1304, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P43 (for B3/62)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1305, 'parking', 'P43', 292) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P43
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1305, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Adil hadaoui
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(293, 'user293@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Adil hadaoui', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/63
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1306, 'apartment', 'B3/63', 293) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/63
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1306, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P100 (for B3/63)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1307, 'parking', 'P100', 293) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P100
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1307, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Rahoui Lamrani ses filles
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(294, 'user294@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Rahoui Lamrani ses filles', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/64
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1308, 'apartment', 'B3/64', 294) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/64
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1308, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: ben yessif Ahmed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(295, 'user295@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'ben yessif Ahmed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/65
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1309, 'apartment', 'B3/65', 295) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/65
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1309, 2026, 6514.54) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P108 (for B3/65)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1310, 'parking', 'P108', 295) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P108
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1310, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Doukkali Idriss
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(296, 'user296@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Doukkali Idriss', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/66
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1311, 'apartment', 'B3/66', 296) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/66
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1311, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: El Khamlichi Ismail
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(297, 'user297@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'El Khamlichi Ismail', '0639544866', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/67
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1312, 'apartment', 'B3/67', 297) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/67
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1312, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P60 (for B3/67)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1313, 'parking', 'P60', 297) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P60
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1313, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aba Yahya Abderrazak
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(298, 'aba61@msn.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aba Yahya Abderrazak', '0063', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/68
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1314, 'apartment', 'B3/68', 298) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/68
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1314, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P47 (for B3/68)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1315, 'parking', 'P47', 298) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P47
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1315, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: zakia khoula
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(299, 'user299@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'zakia khoula', '00447403494446', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/71
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1316, 'apartment', 'B3/71', 299) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/71
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1316, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P76 (for B3/71)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1317, 'parking', 'P76', 299) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P76
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1317, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: loubna et mohamed salah  gallah
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(300, 'user300@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'loubna et mohamed salah  gallah', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/72
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1318, 'apartment', 'B3/72', 300) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/72
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1318, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Fils de Maya Moti Mahtani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(301, 'user301@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Fils de Maya Moti Mahtani', '+447736979133', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/73
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1319, 'apartment', 'B3/73', 301) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/73
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1319, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P132 (for B3/73)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1320, 'parking', 'P132', 301) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P132
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1320, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Boughaba Amina
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(302, 'user302@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Boughaba Amina', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/74
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1321, 'apartment', 'B3/74', 302) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/74
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1321, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Miguel Angel Velasco Garcia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(303, 'user303@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Miguel Angel Velasco Garcia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/75
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1322, 'apartment', 'B3/75', 303) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/75
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1322, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Dominique bourlet
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(304, 'user304@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Dominique bourlet', '+4917671701828', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/76
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1323, 'apartment', 'B3/76', 304) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/76
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1323, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Saida Guertet
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(305, 'user305@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Saida Guertet', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/77
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1324, 'apartment', 'B3/77', 305) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/77
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1324, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P94 (for B3/77)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1325, 'parking', 'P94', 305) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P94
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1325, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ouach Soumya
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(306, 'user306@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ouach Soumya', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/78
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1326, 'apartment', 'B3/78', 306) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/78
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1326, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P93 (for B3/78)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1327, 'parking', 'P93', 306) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P93
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1327, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Fatima Ait Gnaou
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(307, 'user307@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Fatima Ait Gnaou', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/81
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1328, 'apartment', 'B3/81', 307) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/81
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1328, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P120 (for B3/81)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1329, 'parking', 'P120', 307) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P120
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1329, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Boucetta Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(308, 'user308@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Boucetta Mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/82
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1330, 'apartment', 'B3/82', 308) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/82
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1330, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P42 (for B3/82)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1331, 'parking', 'P42', 308) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P42
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1331, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Redouan Jaabak
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(309, 'user309@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Redouan Jaabak', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/83
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1332, 'apartment', 'B3/83', 309) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/83
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1332, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P200 (for B3/83)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1333, 'parking', 'P200', 309) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P200
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1333, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/84
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1334, 'apartment', 'B3/84', 309) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/84
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1334, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P182 (for B3/84)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1335, 'parking', 'P182', 309) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P182
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1335, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: TOKE IBRVE
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(310, 'user310@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'TOKE IBRVE', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/85
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1336, 'apartment', 'B3/85', 310) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/85
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1336, 2026, 6514.56) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Boujouf Said
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(311, 'user311@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Boujouf Said', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/86
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1337, 'apartment', 'B3/86', 311) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/86
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1337, 2026, 4343.04) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ouafaa Fahsi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(312, 'user312@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ouafaa Fahsi', '0661297047', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/87
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1338, 'apartment', 'B3/87', 312) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/87
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1338, 2026, 3981.12) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Laila Wendelen
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(313, 'laila.wendelen@gmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Laila Wendelen', '+32492368956', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/88
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1339, 'apartment', 'B3/88', 313) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/88
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1339, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B3/91
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1340, 'apartment', 'B3/91', 289) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/91
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1340, 2026, 11943.36) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P185 (for B3/91)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1341, 'parking', 'P185', 289) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P185
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1341, 2026, 2224) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sté les Arganiers ADAM
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(314, 'user314@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sté les Arganiers ADAM', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/92
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1342, 'apartment', 'B3/92', 314) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/92
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1342, 2026, 7962.24) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: juan benegas
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(315, 'user315@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'juan benegas', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/93
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1343, 'apartment', 'B3/93', 315) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/93
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1343, 2026, 11581.44) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sté les Arganiers Adam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(316, 'user316@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sté les Arganiers Adam', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/94
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1344, 'apartment', 'B3/94', 316) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/94
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1344, 2026, 7962.24) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mr ziani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(317, 'user317@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mr ziani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B3/95
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1345, 'apartment', 'B3/95', 317) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B3/95
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1345, 2026, 11581.44) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P186 (for B3/95)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1346, 'parking', 'P186', 317) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P186
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1346, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bouid Othman
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(318, 'user318@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bouid Othman', '0032487263730', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/S4
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1347, 'apartment', 'B4/S4', 318) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/S4
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1347, 2026, 7962.24) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Rkaina Saad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(319, 'user319@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Rkaina Saad', '0661173934', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/S3
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1348, 'apartment', 'B4/S3', 319) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/S3
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1348, 2026, 6876.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed boudiab
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(320, 'user320@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed boudiab', '0660612601', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/S2
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1349, 'apartment', 'B4/S2', 320) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/S2
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1349, 2026, 6876.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/S1
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1350, 'apartment', 'B4/S1', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/S1
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1350, 2026, 7238.4) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Glass Plus International Maroc
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(321, 'user321@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Glass Plus International Maroc', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/01
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1351, 'apartment', 'B4/01', 321) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/01
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1351, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P16 (for B4/01)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1352, 'parking', 'P16', 321) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P16
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1352, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Fatima Zahra Laqiasse
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(322, 'user322@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Fatima Zahra Laqiasse', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/02
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1353, 'apartment', 'B4/02', 322) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/02
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1353, 2026, 13752.96) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Naima Fikri ((Gnaou nourddine)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(323, 'user323@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Naima Fikri ((Gnaou nourddine)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/03
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1354, 'apartment', 'B4/03', 323) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/03
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1354, 2026, 8324.16) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P197 (for B4/03)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1355, 'parking', 'P197', 323) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P197
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1355, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Gnaou Sami
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(324, 'user324@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Gnaou Sami', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/04
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1356, 'apartment', 'B4/04', 324) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/04
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1356, 2026, 9048) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P196 (for B4/04)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1357, 'parking', 'P196', 324) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P196
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1357, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohammed Said Erradi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(325, 'user325@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohammed Said Erradi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/05
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1358, 'apartment', 'B4/05', 325) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/05
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1358, 2026, 5790.72) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P195 (for B4/05)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1359, 'parking', 'P195', 325) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P195
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1359, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Younsi Mustapha
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(326, 'user326@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Younsi Mustapha', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/06
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1360, 'apartment', 'B4/06', 326) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/06
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1360, 2026, 6152.64) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P15 (for B4/06)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1361, 'parking', 'P15', 326) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P15
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1361, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: yasser el Ftouh
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(327, 'user327@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'yasser el Ftouh', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1362, 'apartment', 'B4/11', 327) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1362, 2026, 5428.8) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P183 (for B4/11)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1363, 'parking', 'P183', 327) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P183
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1363, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Iraqui Houssaini Noureddine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(328, 'user328@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Iraqui Houssaini Noureddine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1364, 'apartment', 'B4/12', 328) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1364, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P91 (for B4/12)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1365, 'parking', 'P91', 328) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P91
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1365, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Zaami Fatima Zohra
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(329, 'user329@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Zaami Fatima Zohra', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1366, 'apartment', 'B4/13', 329) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1366, 2026, 6152.64) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Samir khamlich
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(330, 'user330@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Samir khamlich', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1367, 'apartment', 'B4/14', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1367, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aziz BEN Zaydan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(331, 'user331@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aziz BEN Zaydan', '0616603446', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1368, 'apartment', 'B4/21', 331) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1368, 2026, 5428.8) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P140 (for B4/21)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1369, 'parking', 'P140', 331) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P140
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1369, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abdelmoula Youssef
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(332, 'user332@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abdelmoula Youssef', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1370, 'apartment', 'B4/22', 332) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1370, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Alaoui abdelkhalak
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(333, 'user333@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Alaoui abdelkhalak', '+32478512702', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1371, 'apartment', 'B4/23', 333) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1371, 2026, 6152.64) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/24
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1372, 'apartment', 'B4/24', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1372, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: FTOUH RABIAA
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(334, 'user334@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'FTOUH RABIAA', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/31
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1373, 'apartment', 'B4/31', 334) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/31
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1373, 2026, 5428.8) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Hadj  ALI omar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(335, 'omaroha2020@gmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Hadj  ALI omar', '0661296097', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/32
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1374, 'apartment', 'B4/32', 335) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/32
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1374, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P136 (for B4/32)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1375, 'parking', 'P136', 335) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P136
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1375, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/33
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1376, 'apartment', 'B4/33', 179) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/33
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1376, 2026, 6152.64) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/34
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1377, 'apartment', 'B4/34', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/34
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1377, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: hicham el maadeqi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(336, 'user336@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'hicham el maadeqi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/41
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1378, 'apartment', 'B4/41', 336) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/41
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1378, 2026, 5428.8) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P131 (for B4/41)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1379, 'parking', 'P131', 336) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P131
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1379, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Tazi Hassan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(337, 'user337@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Tazi Hassan', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/42
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1380, 'apartment', 'B4/42', 337) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/42
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1380, 2026, 5080.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P217 (for B4/42)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1381, 'parking', 'P217', 337) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P217
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1381, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: FATIHA
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(338, 'user338@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'FATIHA', '0667154839', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/43
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1382, 'apartment', 'B4/43', 338) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/43
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1382, 2026, 6152.64) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P202 (for B4/43)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1383, 'parking', 'P202', 338) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P202
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1383, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/44
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1384, 'apartment', 'B4/44', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/44
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1384, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: M,ziani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(339, 'user339@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'M,ziani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/51
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1385, 'apartment', 'B4/51', 339) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/51
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1385, 2026, 5428.8) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P30 (for B4/51)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1386, 'parking', 'P30', 339) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P30
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1386, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/52
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1387, 'apartment', 'B4/52', 235) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/52
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1387, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P170 (for B4/52)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1388, 'parking', 'P170', 235) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P170
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1388, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mostapha tribek
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(340, 'user340@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mostapha tribek', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/53
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1389, 'apartment', 'B4/53', 340) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/53
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1389, 2026, 6137) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P121 (for B4/53)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1390, 'parking', 'P121', 340) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P121
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1390, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/54
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1391, 'apartment', 'B4/54', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/54
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1391, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mustapha Hamdan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(341, 'topcameloclcey@hotmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mustapha Hamdan', '00447599287286', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/61
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1392, 'apartment', 'B4/61', 341) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/61
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1392, 2026, 5428.8) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/62
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1393, 'apartment', 'B4/62', 235) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/62
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1393, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P223 (for B4/62)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1394, 'parking', 'P223', 235) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P223
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1394, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: HAKIM KAMAl
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(342, 'user342@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'HAKIM KAMAl', '0032479382160', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/63
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1395, 'apartment', 'B4/63', 342) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/63
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1395, 2026, 6137) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P173 (for B4/63)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1396, 'parking', 'P173', 342) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P173
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1396, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/64
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1397, 'apartment', 'B4/64', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/64
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1397, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sbai Ismail
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(343, 'user343@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sbai Ismail', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/71
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1398, 'apartment', 'B4/71', 343) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/71
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1398, 2026, 5428.8) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P2 (for B4/71)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1399, 'parking', 'P2', 343) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P2
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1399, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/72
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1400, 'apartment', 'B4/72', 336) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/72
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1400, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P169 (for B4/72)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1401, 'parking', 'P169', 336) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P169
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1401, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Boufedjikh Morad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(344, 'user344@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Boufedjikh Morad', '0033773821335', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/73
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1402, 'apartment', 'B4/73', 344) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/73
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1402, 2026, 6137) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P218 (for B4/73)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1403, 'parking', 'P218', 344) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P218
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1403, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/74
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1404, 'apartment', 'B4/74', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/74
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1404, 2026, 5066.88) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Amrani Amine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(345, 'user345@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Amrani Amine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/81
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1405, 'apartment', 'B4/81', 345) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/81
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1405, 2026, 12996) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P176 (for B4/81)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1406, 'parking', 'P176', 345) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P176
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1406, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: STEPHANE CARRILLON
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(346, 'user346@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'STEPHANE CARRILLON', '0663638054', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/82
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1407, 'apartment', 'B4/82', 346) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/82
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1407, 2026, 12996) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P219 (for B4/82)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1408, 'parking', 'P219', 346) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P219
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1408, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: BASSO GIOVANA
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(347, 'user347@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'BASSO GIOVANA', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B4/84
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1409, 'apartment', 'B4/84', 347) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/84
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1409, 2026, 18050) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P129 (for B4/84)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1410, 'parking', 'P129', 347) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P129
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1410, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P130 (for B4/84)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1411, 'parking', 'P130', 347) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P130
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1411, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B4/85
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1412, 'apartment', 'B4/85', 330) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B4/85
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1412, 2026, 4693) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/S5
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1413, 'apartment', 'B5/S5', NULL) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/S5
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1413, 2026, 6516) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abdelmjid Hannoum
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(348, 'user348@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abdelmjid Hannoum', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/S4
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1414, 'apartment', 'B5/S4', 348) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/S4
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1414, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Chakib et Ali Proubi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(349, 'user349@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Chakib et Ali Proubi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/S3
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1415, 'apartment', 'B5/S3', 349) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/S3
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1415, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P212 (for B5/S3)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1416, 'parking', 'P212', 349) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P212
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1416, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Younsi Farah
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(350, 'user350@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Younsi Farah', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/S2
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1417, 'apartment', 'B5/S2', 350) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/S2
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1417, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P147 (for B5/S2)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1418, 'parking', 'P147', 350) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P147
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1418, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Younsi Ali
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(351, 'user351@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Younsi Ali', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/S1
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1419, 'apartment', 'B5/S1', 351) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/S1
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1419, 2026, 6154) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P145 (for B5/S1)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1420, 'parking', 'P145', 351) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P145
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1420, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/01
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1421, 'apartment', 'B5/01', 231) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/01
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1421, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Loukach Mahdia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(352, 'user352@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Loukach Mahdia', '0668426600', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/2
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1422, 'apartment', 'B5/2', 352) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/2
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1422, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Nadia El Alami
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(353, 'user353@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Nadia El Alami', '0661143650', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/3
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1423, 'apartment', 'B5/3', 353) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/3
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1423, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Jorvan Viera
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(354, 'user354@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Jorvan Viera', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/4
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1424, 'apartment', 'B5/4', 354) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/4
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1424, 2026, 3620) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ismail et Youssef Proubi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(355, 'user355@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ismail et Youssef Proubi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/5
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1425, 'apartment', 'B5/5', 355) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/5
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1425, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Chahir Mohammed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(356, 'user356@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Chahir Mohammed', '0661225932', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/6
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1426, 'apartment', 'B5/6', 356) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/6
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1426, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P72 (for B5/6)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1427, 'parking', 'P72', 356) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P72
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1427, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Alaoui Mostafa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(357, 'user357@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Alaoui Mostafa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/7
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1428, 'apartment', 'B5/7', 357) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/7
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1428, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aoudia Somia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(358, 'user358@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aoudia Somia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1429, 'apartment', 'B5/11', 358) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1429, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Maouane moata
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(359, 'user359@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Maouane moata', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1430, 'apartment', 'B5/12', 359) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1430, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P220 (for B5/12)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1431, 'parking', 'P220', 359) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P220
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1431, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Karim El Janfali
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(360, 'user360@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Karim El Janfali', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1432, 'apartment', 'B5/13', 360) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1432, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P214 (for B5/13)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1433, 'parking', 'P214', 360) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P214
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1433, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mouna et Laila Rkaina
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(361, 'user361@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mouna et Laila Rkaina', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1434, 'apartment', 'B5/14', 361) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1434, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/15
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1435, 'apartment', 'B5/15', 344) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/15
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1435, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/16
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1436, 'apartment', 'B5/16', 322) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/16
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1436, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Majed Ben Mohammed Aldewish
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(362, 'user362@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Majed Ben Mohammed Aldewish', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1437, 'apartment', 'B5/21', 362) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1437, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P210 (for B5/21)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1438, 'parking', 'P210', 362) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P210
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1438, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Joyce  marie Luchka
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(363, 'user363@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Joyce  marie Luchka', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1439, 'apartment', 'B5/22', 363) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1439, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P178 (for B5/22)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1440, 'parking', 'P178', 363) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P178
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1440, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1441, 'apartment', 'B5/23', 231) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1441, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Aribech EL Houcine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(364, 'user364@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Aribech EL Houcine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/24
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1442, 'apartment', 'B5/24', 364) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1442, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/25
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1443, 'apartment', 'B5/25', 157) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/25
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1443, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Karima abdelaziz
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(365, 'user365@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Karima abdelaziz', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/26
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1444, 'apartment', 'B5/26', 365) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/26
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1444, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/31
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1445, 'apartment', 'B5/31', 304) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/31
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1445, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: arfaoui Fouad
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(366, 'user366@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'arfaoui Fouad', '0648558451', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/32
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1446, 'apartment', 'B5/32', 366) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/32
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1446, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P13 (for B5/32)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1447, 'parking', 'P13', 366) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1447, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abkari zoubida
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(367, 'user367@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abkari zoubida', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/33
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1448, 'apartment', 'B5/33', 367) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/33
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1448, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P37 (for B5/33)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1449, 'parking', 'P37', 367) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P37
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1449, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Lluvia Rojo Moro
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(368, 'user368@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Lluvia Rojo Moro', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/34
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1450, 'apartment', 'B5/34', 368) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/34
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1450, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P221 (for B5/34)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1451, 'parking', 'P221', 368) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P221
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1451, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sehnoun Sara et Rhita
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(369, 'user369@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sehnoun Sara et Rhita', '0661331033', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/35
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1452, 'apartment', 'B5/35', 369) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/35
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1452, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ait Brahim Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(370, 'user370@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ait Brahim Mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/36
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1453, 'apartment', 'B5/36', 370) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/36
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1453, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P138 (for B5/36)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1454, 'parking', 'P138', 370) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P138
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1454, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Graeme Paul Gentry/  (plasson chantal locataire )
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(371, 'user371@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Graeme Paul Gentry/  (plasson chantal locataire )', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/41
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1455, 'apartment', 'B5/41', 371) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/41
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1455, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P171 (for B5/41)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1456, 'parking', 'P171', 371) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P171
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1456, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Kerouani abdeslam
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(372, 'user372@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Kerouani abdeslam', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/42
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1457, 'apartment', 'B5/42', 372) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/42
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1457, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P92 (for B5/42)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1458, 'parking', 'P92', 372) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P92
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1458, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Laghrich Ilham
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(373, 'user373@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Laghrich Ilham', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/43
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1459, 'apartment', 'B5/43', 373) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/43
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1459, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P119 (for B5/43)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1460, 'parking', 'P119', 373) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P119
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1460, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Filali ahmed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(374, 'user374@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Filali ahmed', '0666087778', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/44
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1461, 'apartment', 'B5/44', 374) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/44
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1461, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P234 (for B5/44)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1462, 'parking', 'P234', 374) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P234
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1462, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Larhrissi amal
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(375, 'user375@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Larhrissi amal', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/45
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1463, 'apartment', 'B5/45', 375) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/45
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1463, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: charekkaoui
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(376, 'user376@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'charekkaoui', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/46
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1464, 'apartment', 'B5/46', 376) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/46
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1464, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Latifa Kharja
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(377, 'user377@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Latifa Kharja', '0665846638', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/51
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1465, 'apartment', 'B5/51', 377) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/51
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1465, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P141 (for B5/51)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1466, 'parking', 'P141', 377) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P141
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1466, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/52
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1467, 'apartment', 'B5/52', 327) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/52
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1467, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ahmed barkouta
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(378, 'user378@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ahmed barkouta', '0664839951', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/53
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1468, 'apartment', 'B5/53', 378) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/53
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1468, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P12 (for B5/53)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1469, 'parking', 'P12', 378) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1469, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mounir Zahouani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(379, 'user379@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mounir Zahouani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/54
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1470, 'apartment', 'B5/54', 379) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/54
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1470, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P240 (for B5/54)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1471, 'parking', 'P240', 379) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P240
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1471, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Jamal El mokkadem
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(380, 'user380@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Jamal El mokkadem', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/55
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1472, 'apartment', 'B5/55', 380) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/55
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1472, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Chraibi Kaadoud Mekki
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(381, 'user381@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Chraibi Kaadoud Mekki', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/56
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1473, 'apartment', 'B5/56', 381) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/56
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1473, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Abd saddak
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(382, 'user382@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Abd saddak', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/61
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1474, 'apartment', 'B5/61', 382) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/61
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1474, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P174 (for B5/61)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1475, 'parking', 'P174', 382) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P174
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1475, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: B5/62
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1476, 'apartment', 'B5/62', 321) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/62
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1476, 2026, 5068) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P14 (for B5/62)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1477, 'parking', 'P14', 321) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1477, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Khalid Ezzaki
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(383, 'user383@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Khalid Ezzaki', '0620543248', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/63
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1478, 'apartment', 'B5/63', 383) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/63
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1478, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed Hamich
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(384, 'user384@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed Hamich', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/64
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1479, 'apartment', 'B5/64', 384) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/64
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1479, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P35 (for B5/64)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1480, 'parking', 'P35', 384) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P35
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1480, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: aharchi khalid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(385, 'user385@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'aharchi khalid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/65
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1481, 'apartment', 'B5/65', 385) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/65
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1481, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: solaiman Touzani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(386, 'user386@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'solaiman Touzani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/66
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1482, 'apartment', 'B5/66', 386) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/66
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1482, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: MOUDDEN ABDELLAH
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(387, 'user387@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'MOUDDEN ABDELLAH', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/71
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1483, 'apartment', 'B5/71', 387) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/71
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1483, 2026, 5792) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P118 (for B5/71)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1484, 'parking', 'P118', 387) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P118
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1484, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Yasser El Ftouh et Youssef El Ftouh
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(388, 'user388@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Yasser El Ftouh et Youssef El Ftouh', '0674905657', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/73
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1485, 'apartment', 'B5/73', 388) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/73
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1485, 2026, 9050) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P3 (for B5/73)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1486, 'parking', 'P3', 388) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P3
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1486, 2026, 6896) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P4 (for B5/73)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1487, 'parking', 'P4', 388) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P4
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1487, 2026, 6896) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P5 (for B5/73)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1488, 'parking', 'P5', 388) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P5
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1488, 2026, 6896) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P9 (for B5/73)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1489, 'parking', 'P9', 388) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P9
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1489, 2026, 6896) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: sté EL Faddane/Mr alamrani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(389, 'user389@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'sté EL Faddane/Mr alamrani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/74
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1490, 'apartment', 'B5/74', 389) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/74
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1490, 2026, 5430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Anas
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(390, 'user390@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Anas', '+33758018383', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/75
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1491, 'apartment', 'B5/75', 390) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/75
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1491, 2026, 4706) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Hassan el mokkaden
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(391, 'user391@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Hassan el mokkaden', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/76
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1492, 'apartment', 'B5/76', 391) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/76
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1492, 2026, 3982) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Amina Regragui et Aissa Belhadj
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(392, 'user392@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Amina Regragui et Aissa Belhadj', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/81
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1493, 'apartment', 'B5/81', 392) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/81
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1493, 2026, 12670) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Bouchera radi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(393, 'user393@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Bouchera radi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/82
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1494, 'apartment', 'B5/82', 393) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/82
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1494, 2026, 25702) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Christian Raffin
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(394, 'user394@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Christian Raffin', '0644822321', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/83
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1495, 'apartment', 'B5/83', 394) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/83
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1495, 2026, 6878) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P36 (for B5/83)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1496, 'parking', 'P36', 394) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P36
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1496, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Jukka kaleva
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(395, 'user395@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Jukka kaleva', '0661313557', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: B5/84
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1497, 'apartment', 'B5/84', 395) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment B5/84
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1497, 2026, 12670) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P162 (for B5/84)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1498, 'parking', 'P162', 395) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P162
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1498, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Doukkali Rachid
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(396, 'user396@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Doukkali Rachid', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/01
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1499, 'apartment', 'G1/01', 396) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/01
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1499, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P125 (for G1/01)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1500, 'parking', 'P125', 396) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P125
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1500, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: latifa khayati
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(397, 'user397@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'latifa khayati', '0031682808431', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/02
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1501, 'apartment', 'G1/02', 397) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/02
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1501, 2026, 8466) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P81 (for G1/02)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1502, 'parking', 'P81', 397) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P81
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1502, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: HASSAN ZEYAD
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(398, 'user398@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'HASSAN ZEYAD', '0661718110', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/3
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1503, 'apartment', 'G1/3', 398) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/3
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1503, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P82 (for G1/3)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1504, 'parking', 'P82', 398) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P82
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1504, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: loukili hassani intissar
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(399, 'user399@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'loukili hassani intissar', '0661815082', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G2/4
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1505, 'apartment', 'G2/4', 399) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G2/4
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1505, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P83 (for G2/4)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1506, 'parking', 'P83', 399) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P83
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1506, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mouji Ahmed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(400, 'user400@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mouji Ahmed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G2/5
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1507, 'apartment', 'G2/5', 400) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G2/5
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1507, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P237 (for G2/5)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1508, 'parking', 'P237', 400) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P237
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1508, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Larhoussi Ahmed Amine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(401, 'user401@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Larhoussi Ahmed Amine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/6
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1509, 'apartment', 'G1/6', 401) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/6
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1509, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Najia, Malika Alaoui Ismaili
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(402, 'user402@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Najia, Malika Alaoui Ismaili', '0675703227', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/7
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1510, 'apartment', 'G1/7', 402) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/7
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1510, 2026, 8466) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: hikmat boudraa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(403, 'user403@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'hikmat boudraa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/8
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1511, 'apartment', 'G1/8', 403) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/8
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1511, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Alaoui Fatima Zohra/Sara Maatouk
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(404, 'user404@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Alaoui Fatima Zohra/Sara Maatouk', '0661198561', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G2/9
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1512, 'apartment', 'G2/9', 404) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G2/9
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1512, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: JIHANE
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(405, 'user405@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'JIHANE', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G2/10
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1513, 'apartment', 'G2/10', 405) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G2/10
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1513, 2026, 5478) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Youness Thifa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(406, 'user406@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Youness Thifa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1514, 'apartment', 'G1/11', 406) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1514, 2026, 17430) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P107 (for G1/11)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1515, 'parking', 'P107', 406) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P107
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1515, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sedqi Habiba et Sedqi Seddik (Notair)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(407, 'user407@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sedqi Habiba et Sedqi Seddik (Notair)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1516, 'apartment', 'G1/12', 407) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1516, 2026, 15438) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P184 (for G1/12)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1517, 'parking', 'P184', 407) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P184
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1517, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Amina Boucheouat
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(408, 'user408@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Amina Boucheouat', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G1/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1518, 'apartment', 'G1/13', 408) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G1/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1518, 2026, 6474) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P65 (for G1/13)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1519, 'parking', 'P65', 408) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P65
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1519, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: G2/15
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1520, 'apartment', 'G2/15', 112) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G2/15
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1520, 2026, 13446) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P32 (for G2/15)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1521, 'parking', 'P32', 112) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P32
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1521, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P33 (for G2/15)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1522, 'parking', 'P33', 112) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P33
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1522, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: HICHAM BEN LACHEGAR
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(409, 'user409@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'HICHAM BEN LACHEGAR', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: G2/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1523, 'apartment', 'G2/14', 409) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment G2/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1523, 2026, 12948) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: RAchid Agzenay
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(410, 'user410@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'RAchid Agzenay', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D1/12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1524, 'apartment', 'D1/12', 410) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D1/12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1524, 2026, 3729) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P75 (for D1/12)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1525, 'parking', 'P75', 410) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P75
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1525, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed akdi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(411, 'user411@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed akdi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D1/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1526, 'apartment', 'D1/13', 411) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D1/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1526, 2026, 3390) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Amina Bkoussa
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(412, 'user412@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Amina Bkoussa', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D1/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1527, 'apartment', 'D1/14', 412) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D1/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1527, 2026, 4407) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: PROARGOS
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(413, 'user413@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'PROARGOS', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D1/22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1528, 'apartment', 'D1/22', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D1/22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1528, 2026, 5085) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P143 (for D1/22)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1529, 'parking', 'P143', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P143
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1529, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: D1/23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1530, 'apartment', 'D1/23', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D1/23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1530, 2026, 5424) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Hamouti Mimoun
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(414, 'user414@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Hamouti Mimoun', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D2/08
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1531, 'apartment', 'D2/08', 414) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D2/08
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1531, 2026, 3729) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohcine Harras
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(415, 'user415@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohcine Harras', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D2/09
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1532, 'apartment', 'D2/09', 415) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D2/09
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1532, 2026, 3390) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mtioui Belayachi Adnane
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(416, 'user416@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mtioui Belayachi Adnane', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D2/10
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1533, 'apartment', 'D2/10', 416) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D2/10
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1533, 2026, 3051) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: EL Khamlichi Ismail
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(417, 'user417@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'EL Khamlichi Ismail', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D2/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1534, 'apartment', 'D2/11', 417) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D2/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1534, 2026, 3729) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P49 (for D2/11)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1535, 'parking', 'P49', 417) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P49
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1535, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: D2/20
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1536, 'apartment', 'D2/20', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D2/20
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1536, 2026, 4746) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: D2/21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1537, 'apartment', 'D2/21', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D2/21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1537, 2026, 5085) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P142 (for D2/21)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1538, 'parking', 'P142', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P142
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1538, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: hamza boulaiche
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(418, 'user418@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'hamza boulaiche', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D3/04
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1539, 'apartment', 'D3/04', 418) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D3/04
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1539, 2026, 3051) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Arosi Said Nourdine
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(419, 'user419@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Arosi Said Nourdine', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D3/05
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1540, 'apartment', 'D3/05', 419) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D3/05
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1540, 2026, 3051) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Fadoua Chantouf
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(420, 'user420@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Fadoua Chantouf', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D3/06
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1541, 'apartment', 'D3/06', 420) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D3/06
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1541, 2026, 2712) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Zohra Mebroud
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(421, 'user421@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Zohra Mebroud', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D3/07
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1542, 'apartment', 'D3/07', 421) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D3/07
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1542, 2026, 3390) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Nabil azab
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(422, 'user422@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Nabil azab', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D3/18
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1543, 'apartment', 'D3/18', 422) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D3/18
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1543, 2026, 4407) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P106 (for D3/18)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1544, 'parking', 'P106', 422) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P106
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1544, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: mohamed azab
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(423, 'user423@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'mohamed azab', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D3/19
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1545, 'apartment', 'D3/19', 423) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D3/19
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1545, 2026, 4746) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P105 (for D3/19)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1546, 'parking', 'P105', 423) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P105
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1546, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Oumarir Mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(424, 'user424@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Oumarir Mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D4/01
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1547, 'apartment', 'D4/01', 424) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D4/01
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1547, 2026, 3729) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Zoubida El Jazouly & Adam Bennani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(425, 'user425@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Zoubida El Jazouly & Adam Bennani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D4-02
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1548, 'apartment', 'D4-02', 425) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D4-02
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1548, 2026, 3390) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Morar Ashish
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(426, 'user426@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Morar Ashish', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D4-03
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1549, 'apartment', 'D4-03', 426) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D4-03
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1549, 2026, 5085) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P123 (for D4-03)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1550, 'parking', 'P123', 426) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P123
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1550, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Lahmami abdelrhani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(427, 'user427@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Lahmami abdelrhani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D4/16
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1551, 'apartment', 'D4/16', 427) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D4/16
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1551, 2026, 2373) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: said Sellak
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(428, 'user428@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'said Sellak', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: D4/17
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1552, 'apartment', 'D4/17', 428) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment D4/17
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1552, 2026, 3390) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: abdelali Radi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(429, 'user429@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'abdelali Radi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E'01
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1553, 'apartment', 'E\'01', 429) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E'01
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1553, 2026, 5648) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P128 (for E'01)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1554, 'parking', 'P128', 429) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P128
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1554, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Chahidi Abderahim
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(430, 'user430@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Chahidi Abderahim', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E'02
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1555, 'apartment', 'E\'02', 430) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E'02
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1555, 2026, 5648) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P127 (for E'02)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1556, 'parking', 'P127', 430) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P127
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1556, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Alaoui Abdelkhalak
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(431, 'user431@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Alaoui Abdelkhalak', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E'03
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1557, 'apartment', 'E\'03', 431) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E'03
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1557, 2026, 5648) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P48 (for E'03)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1558, 'parking', 'P48', 431) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P48
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1558, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Boujaada el hassan
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(432, 'user432@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Boujaada el hassan', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E'04
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1559, 'apartment', 'E\'04', 432) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E'04
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1559, 2026, 5648) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P126 (for E'04)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1560, 'parking', 'P126', 432) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P126
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1560, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: HARTI NAIMA
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(433, 'user433@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'HARTI NAIMA', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E'05
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1561, 'apartment', 'E\'05', 433) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E'05
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1561, 2026, 10237) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P20 (for E'05)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1562, 'parking', 'P20', 433) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P20
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1562, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: hakim
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(434, 'user434@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'hakim', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E'06
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1563, 'apartment', 'E\'06', 434) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E'06
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1563, 2026, 11489) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P85 (for E'06)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1564, 'parking', 'P85', 434) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P85
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1564, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E1/01
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1565, 'apartment', 'E1/01', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/01
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1565, 2026, 6707) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E1/02
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1566, 'apartment', 'E1/02', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/02
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1566, 2026, 4589) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mohamed EL Jattari
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(435, 'user435@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mohamed EL Jattari', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E1/07
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1567, 'apartment', 'E1/07', 435) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/07
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1567, 2026, 7060) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mesbah Boulaich Rabia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(436, 'user436@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mesbah Boulaich Rabia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E1/08
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1568, 'apartment', 'E1/08', 436) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/08
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1568, 2026, 4236) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Touhfa EL Mejdoubi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(437, 'user437@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Touhfa EL Mejdoubi', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E1/13
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1569, 'apartment', 'E1/13', 437) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/13
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1569, 2026, 7060) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Ourida Kebour
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(438, 'user438@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Ourida Kebour', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E1/14
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1570, 'apartment', 'E1/14', 438) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/14
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1570, 2026, 4236) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E1/19
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1571, 'apartment', 'E1/19', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/19
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1571, 2026, 10943) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E1/20
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1572, 'apartment', 'E1/20', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E1/20
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1572, 2026, 7413) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Rachid Matlaoui
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(439, 'user439@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Rachid Matlaoui', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E2/03
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1573, 'apartment', 'E2/03', 439) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/03
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1573, 2026, 6707) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P95 (for E2/03)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1574, 'parking', 'P95', 439) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P95
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1574, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E2/04
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1575, 'apartment', 'E2/04', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/04
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1575, 2026, 7413) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Zohra Baroudi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(440, 'user440@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Zohra Baroudi', '0663340611', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E2/09
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1576, 'apartment', 'E2/09', 440) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/09
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1576, 2026, 4236) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: khadouj Ezzouaouy
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(441, 'user441@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'khadouj Ezzouaouy', '+32493086827', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E2/10
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1577, 'apartment', 'E2/10', 441) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/10
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1577, 2026, 5295) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Imane Trachli ET Souad Azmi
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(442, '0642756015abd.trachli@gmail.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Imane Trachli ET Souad Azmi', '0642756015', 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E2/15
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1578, 'apartment', 'E2/15', 442) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/15
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1578, 2026, 4236) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Amrani
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(443, 'user443@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Amrani', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E2/16
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1579, 'apartment', 'E2/16', 443) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/16
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1579, 2026, 5295) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E2/21
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1580, 'apartment', 'E2/21', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/21
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1580, 2026, 7413) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P71 (for E2/21)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1581, 'parking', 'P71', 413) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P71
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1581, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E2/22
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1582, 'apartment', 'E2/22', 443) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E2/22
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1582, 2026, 10007.28) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P25 (for E2/22)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1583, 'parking', 'P25', 443) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P25
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1583, 2026, 2586) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: GNAOU
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(444, 'user444@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'GNAOU', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Apartment: E3/05
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1584, 'apartment', 'E3/05', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/05
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1584, 2026, 9178) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E3/06
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1585, 'apartment', 'E3/06', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/06
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1585, 2026, 9475.44) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P53 (for E3/06)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1586, 'parking', 'P53', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P53
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1586, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E3/11
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1587, 'apartment', 'E3/11', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/11
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1587, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E3/12
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1588, 'apartment', 'E3/12', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/12
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1588, 2026, 6559.92) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P27 (for E3/12)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1589, 'parking', 'P27', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P27
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1589, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E3/17
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1590, 'apartment', 'E3/17', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/17
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1590, 2026, 6195.48) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P29 (for E3/17)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1591, 'parking', 'P29', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P29
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1591, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E3/18
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1592, 'apartment', 'E3/18', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/18
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1592, 2026, 6559.92) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E3/23
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1593, 'apartment', 'E3/23', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/23
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1593, 2026, 11297.64) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P26 (for E3/23)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1594, 'parking', 'P26', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P26
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1594, 2026, 3448) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Apartment: E3/24
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1595, 'apartment', 'E3/24', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for apartment E3/24
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1595, 2026, 11662.08) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- Parking: P28 (for E3/24)
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1596, 'parking', 'P28', 444) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P28
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1596, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Sky New (Centre Commercial)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(445, 'user445@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Sky New (Centre Commercial)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- User: Salah Eddin Othman Alwat( centre Ccl)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(446, 'user446@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Salah Eddin Othman Alwat( centre Ccl)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Parking: P175 (owner: Salah Eddin Othman Alwat( centre Ccl))
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1597, 'parking', 'P175', 446) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P175
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1597, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Er-rachidi Ahmed (Café Terrasse de Bd)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(447, 'user447@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Er-rachidi Ahmed (Café Terrasse de Bd)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- User: Illusion Maroc ( Café Passion)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(448, 'user448@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Illusion Maroc ( Café Passion)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- User: Daswani Manoj ( EL Hindawi)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(449, 'user449@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Daswani Manoj ( EL Hindawi)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Parking: P199 (owner: Daswani Manoj ( EL Hindawi))
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1598, 'parking', 'P199', 449) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P199
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1598, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Chalbate yassine (Centre Ccl)
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(450, 'user450@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Chalbate yassine (Centre Ccl)', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- Parking: P166 (owner: Chalbate yassine (Centre Ccl))
INSERT INTO properties (id, type, identifier, user_id) VALUES 
(1599, 'parking', 'P166', 450) ON DUPLICATE KEY UPDATE user_id = COALESCE(VALUES(user_id), user_id);

-- Cotisation 2026 for parking P166
INSERT INTO cotisations (property_id, year, amount_due) VALUES 
(1599, 2026, 1724) 
ON DUPLICATE KEY UPDATE amount_due = VALUES(amount_due);

-- User: Mr mohamed
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(451, 'user451@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Mr mohamed', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);

-- User: Hassane Comedia
INSERT INTO users (id, email, password, name, phone, role, status) VALUES 
(452, 'user452@ctb.local', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Hassane Comedia', NULL, 'resident', 'active') ON DUPLICATE KEY UPDATE name = VALUES(name), phone = COALESCE(VALUES(phone), phone);


SET FOREIGN_KEY_CHECKS = 1;
