-- ============================================================
-- Cloudflare D1 SQL Seed Data (100% SQLite / D1 Compatible)
-- ============================================================

-- ROLES
INSERT INTO roles (id, name, slug, description, is_system) VALUES
(1, 'Super Admin',  'super_admin',  'Full system access, overrides all permissions', 1),
(2, 'Admin',        'admin',        'Full tenant access, can manage all data', 1),
(3, 'Manager',      'manager',      'Can manage most operations except billing', 1),
(4, 'Staff',        'staff',        'Limited operational access', 1),
(5, 'Viewer',       'viewer',       'Read-only access to permitted modules', 1),
(6, 'Merchant',     'merchant',     'Merchant owner access to own data', 0);

-- PERMISSIONS (4 actions × 9 modules = 36)
INSERT INTO permissions (name, slug, module, action) VALUES
('View Merchants',   'merchants.view',   'merchants', 'view'),
('Create Merchants', 'merchants.create', 'merchants', 'create'),
('Edit Merchants',   'merchants.edit',   'merchants', 'edit'),
('Delete Merchants', 'merchants.delete', 'merchants', 'delete'),
('View Bookings',    'bookings.view',    'bookings',  'view'),
('Create Bookings',  'bookings.create',  'bookings',  'create'),
('Edit Bookings',    'bookings.edit',    'bookings',  'edit'),
('Delete Bookings',  'bookings.delete',  'bookings',  'delete'),
('View Reviews',     'reviews.view',     'reviews',   'view'),
('Create Reviews',   'reviews.create',   'reviews',   'create'),
('Edit Reviews',     'reviews.edit',     'reviews',   'edit'),
('Delete Reviews',   'reviews.delete',   'reviews',   'delete'),
('View Settlement',  'settlement.view',  'settlement','view'),
('Create Settlement','settlement.create','settlement','create'),
('Edit Settlement',  'settlement.edit',  'settlement','edit'),
('Delete Settlement','settlement.delete','settlement','delete'),
('View Ads',         'ads.view',         'ads',       'view'),
('Create Ads',       'ads.create',       'ads',       'create'),
('Edit Ads',         'ads.edit',         'ads',       'edit'),
('Delete Ads',       'ads.delete',       'ads',       'delete'),
('View Content',     'content.view',     'content',   'view'),
('Create Content',   'content.create',   'content',   'create'),
('Edit Content',     'content.edit',     'content',   'edit'),
('Delete Content',   'content.delete',   'content',   'delete'),
('View Reports',     'reports.view',     'reports',   'view'),
('Create Reports',   'reports.create',   'reports',   'create'),
('Edit Reports',     'reports.edit',     'reports',   'edit'),
('Delete Reports',   'reports.delete',   'reports',   'delete'),
('View Permissions', 'permissions.view', 'permissions','view'),
('Create Permissions','permissions.create','permissions','create'),
('Edit Permissions', 'permissions.edit', 'permissions','edit'),
('Delete Permissions','permissions.delete','permissions','delete'),
('View Settings',    'settings.view',    'settings',  'view'),
('Create Settings',  'settings.create',  'settings',  'create'),
('Edit Settings',    'settings.edit',    'settings',  'edit'),
('Delete Settings',  'settings.delete',  'settings',  'delete');

-- ROLE_PERMISSIONS
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions;

INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE slug NOT IN ('permissions.create','permissions.edit','permissions.delete','settings.delete');

INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions
WHERE slug IN (
  'merchants.view','bookings.view','bookings.edit',
  'reviews.view','ads.view','content.view','reports.view'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions
WHERE action = 'view';

-- DEFAULT TENANT
INSERT INTO tenants (id, name, slug, status) VALUES
(1, 'Company 2', 'company2', 'active');

-- USERS (Password hash for testing)
INSERT INTO users (id, tenant_id, name, email, password, is_super_admin, status, email_verified_at) VALUES
(1, 1, 'Super Admin', 'admin@company2.sa',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 1, 'active', CURRENT_TIMESTAMP);

INSERT INTO users (id, tenant_id, name, email, password, is_super_admin, status) VALUES
(2, 1, 'Test AI User', 'test@example.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 0, 'active');

-- Assign roles
INSERT INTO user_roles (user_id, role_id, tenant_id) VALUES (1, 2, 1);
INSERT INTO user_roles (user_id, role_id, tenant_id) VALUES (2, 3, 1);

-- DOMAINS
INSERT INTO domains (domain, tenant_id, type, is_primary, status) VALUES
('localhost',         1, 'main',      1, 'active'),
('127.0.0.1',         1, 'main',      0, 'active'),
('company2.local',    1, 'main',      0, 'active'),
('company2.sa',       1, 'main',      0, 'active'),
('www.company2.sa',   1, 'subdomain', 0, 'active'),
('app.company2.sa',   1, 'subdomain', 0, 'active');

-- CATEGORIES
INSERT INTO categories (tenant_id, name, slug, icon, sort_order, status) VALUES
(1, 'Food & Beverage',   'food-beverage',   '🍔', 1, 'active'),
(1, 'Retail',            'retail',          '🛍️', 2, 'active'),
(1, 'Technology',        'technology',      '💻', 3, 'active'),
(1, 'Healthcare',        'healthcare',      '🏥', 4, 'active'),
(1, 'Beauty & Wellness', 'beauty-wellness', '💆', 5, 'active'),
(1, 'Sports & Fitness',  'sports-fitness',  '🏋️', 6, 'inactive'),
(1, 'Education',         'education',       '📚', 7, 'active'),
(1, 'Automotive',        'automotive',      '🚗', 8, 'active');

-- MERCHANTS
INSERT INTO merchants (tenant_id, name, email, phone, category_id, status, joined_date) VALUES
(1, 'Acme Store',       'acme@store.com',     '+966501111111', 2, 'active',   '2025-01-10'),
(1, 'Beta Eats',        'beta@eats.com',      '+966502222222', 1, 'active',   '2025-02-14'),
(1, 'Gamma Tech',       'g@tech.com',         '+966503333333', 3, 'pending',  '2025-01-28'),
(1, 'Delta Health',     'dh@health.sa',       '+966504444444', 4, 'inactive', '2024-11-05'),
(1, 'Epsilon Services', 'eps@srv.com',        '+966505555555', 7, 'active',   '2025-03-01'),
(1, 'Zeta Fashion',     'zeta@fashion.sa',    '+966506666666', 2, 'active',   '2025-03-18');

-- SERVICES
INSERT INTO services (tenant_id, merchant_id, category_id, name, description, price, duration_min, tags, is_active) VALUES
(1, 2, 1, 'Gourmet Burger',    'Premium gourmet burger meal',    45.00, 30,  '["featured","new"]',      1),
(1, 3, 3, 'Laptop Repair',     'Professional laptop repair',    150.00, 60,  '["premium"]',             1),
(1, 4, 4, 'Full Body Checkup', 'Complete health checkup',       200.00, 120, '["essential"]',           0),
(1, 6, 5, 'Hair & Styling',    'Professional hair & styling',    80.00, 60,  '["trending","featured"]', 1),
(1, 5, 7, 'Personal Training', 'One-on-one fitness training',   120.00, 60,  '["premium"]',             1);

-- BOOKINGS
INSERT INTO bookings (tenant_id, service_id, merchant_id, customer_name, customer_email, booking_date, booking_time, status, total_price) VALUES
(1, 4, 6, 'Ahmed Saleh', 'ahmed@mail.com', '2025-04-03', '09:00:00', 'confirmed',  80.00),
(1, 3, 4, 'Sara Ali',    'sara@ex.com',    '2025-04-03', '11:30:00', 'pending',   200.00),
(1, 5, 5, 'Omar Khalid', 'omar@corp.sa',   '2025-04-07', '14:00:00', 'confirmed', 120.00),
(1, 1, 2, 'Fatima M.',   'fatima@m.com',   '2025-04-10', '10:00:00', 'cancelled',  45.00),
(1, 2, 3, 'Hassan R.',   'hassan@r.com',   '2025-04-10', '13:00:00', 'pending',   150.00),
(1, 4, 6, 'Noura K.',    'noura@k.com',    '2025-04-15', '09:30:00', 'confirmed',  80.00);

-- REVIEWS


INSERT INTO reviews (tenant_id, merchant_id, author_name, author_email, rating, text, status) VALUES
(1, 2, 'Ahmed S.',   'ahmed@mail.com', 5, 'Amazing food and super fast delivery! Highly recommended.', 'approved'),
(1, 6, 'Sara M.',    'sara@ex.com',    4, 'Great selection of styles. The staff was very helpful.',    'pending'),
(1, 3, 'Omar K.',    'omar@corp.sa',   2, 'Repair took too long and the communication was poor.',      'pending'),
(1, 4, 'Fatima R.',  'fatima@r.com',   5, 'Professional doctors and clean environment. 10/10!',        'approved'),
(1, 5, 'Khalid A.',  'k@corp.sa',      1, 'Spam review with inappropriate content.',                   'rejected');

INSERT INTO contact_messages (tenant_id, name, email, subject, message, is_read) VALUES
(1, 'Ahmed Al-Harbi', 'ahmed@mail.com', 'Issue with booking',     'I booked a session yesterday but received no confirmation email. Can you please check the status of booking #1023?', 0),
(1, 'Sara Khalid',    'sara@ex.com',    'Partnership inquiry',    'We represent a chain of 15 restaurants and would like to discuss a bulk merchant registration.', 1),
(1, 'Omar Faris',     'omar@corp.sa',   'Billing question',       'I was charged twice for the same subscription. Please review transaction ID TX-8847.', 0);

INSERT INTO pricing_plans (tenant_id, name, slug, description, monthly_price, annual_price, features, max_merchants, max_bookings, is_featured, is_active, sort_order) VALUES
(1, 'Basic', 'basic', 'Perfect for small businesses just starting out.', 49.00, 470.00,
 '[{"label":"Up to 5 merchants","included":true},{"label":"100 bookings/month","included":true},{"label":"Basic analytics","included":true},{"label":"Email support","included":true},{"label":"Custom domain","included":false},{"label":"API access","included":false}]',
 5, 100, 0, 1, 1),
(1, 'Pro', 'pro', 'For growing businesses with more needs.', 149.00, 1430.00,
 '[{"label":"Up to 50 merchants","included":true},{"label":"Unlimited bookings","included":true},{"label":"Advanced analytics","included":true},{"label":"Priority support","included":true},{"label":"Custom domain","included":true},{"label":"API access","included":false}]',
 50, NULL, 1, 1, 2),
(1, 'Enterprise', 'enterprise', 'Full-featured for large operations.', 399.00, 3830.00,
 '[{"label":"Unlimited merchants","included":true},{"label":"Unlimited bookings","included":true},{"label":"Full analytics suite","included":true},{"label":"24/7 dedicated support","included":true},{"label":"Custom domain","included":true},{"label":"API access","included":true}]',
 NULL, NULL, 0, 0, 3);

INSERT INTO ads (tenant_id, name, type, start_date, end_date, impressions, clicks, status) VALUES
(1, 'Summer Sale 2025',    'banner',    '2025-06-01', '2025-06-30', 45200, 1830, 'scheduled'),
(1, 'New Merchant Promo',  'sponsored', '2025-04-01', '2025-04-15', 18700,  920, 'active'),
(1, 'App Download Push',   'push',      '2025-03-10', '2025-03-25', 32000, 2450, 'inactive'),
(1, 'Eid Offers',          'video',     '2025-03-28', '2025-04-05', 61000, 4200, 'active');

INSERT INTO content_pages (tenant_id, title, slug, content, meta_description, status) VALUES
(1, 'About Us',       'about-us',  'We are Company 2, a leading platform connecting merchants and customers across Saudi Arabia.', 'Learn more about Company 2 and our mission.', 'published'),
(1, 'Terms of Service','terms',    'By using our platform you agree to these terms and conditions...', 'Company 2 terms of service.', 'published'),
(1, 'Privacy Policy', 'privacy',   'Your privacy is important to us. This policy outlines how we collect and use your data...', 'Company 2 privacy policy.', 'published'),
(1, 'FAQ',            'faq',       'Frequently Asked Questions about Company 2...', 'Get answers to common questions.', 'draft'),
(1, 'Careers',        'careers',   'Join our growing team and help shape the future of commerce in Saudi Arabia...', 'Career opportunities at Company 2.', 'draft');

INSERT INTO settlements (tenant_id, merchant_id, reference_no, amount, fee, net_amount, method, status, period_start, period_end, paid_at) VALUES
(1, 2, 'STL-301', 8450.00, 845.00, 7605.00, 'bank_transfer', 'paid',       '2025-03-01', '2025-03-31', '2025-04-01 10:00:00'),
(1, 1, 'STL-302', 3200.00, 320.00, 2880.00, 'bank_transfer', 'paid',       '2025-03-01', '2025-03-31', '2025-04-01 10:00:00'),
(1, 6, 'STL-303', 5600.00, 560.00, 5040.00, 'sadad',         'processing', '2025-04-01', '2025-04-30', NULL),
(1, 3, 'STL-304', 2100.00, 210.00, 1890.00, 'bank_transfer', 'pending',    '2025-04-01', '2025-04-30', NULL),
(1, 5, 'STL-305', 4750.00, 475.00, 4275.00, 'sadad',         'pending',    '2025-04-01', '2025-04-30', NULL),
(1, 4, 'STL-306', 6300.00, 630.00, 5670.00, 'bank_transfer', 'paid',       '2025-03-01', '2025-03-31', '2025-03-30 09:00:00'),
(1, 2, 'STL-307', 7100.00, 710.00, 6390.00, 'bank_transfer', 'paid',       '2025-02-01', '2025-02-28', '2025-03-01 10:00:00');

INSERT INTO notification_settings (user_id, tenant_id, type, label, channel, is_enabled) VALUES
(1, 1, 'new_booking',   'New Booking',           'email',  1),
(1, 1, 'new_booking',   'New Booking',           'in_app', 1),
(1, 1, 'new_review',    'New Review',            'email',  1),
(1, 1, 'new_review',    'New Review',            'in_app', 1),
(1, 1, 'payment',       'Payment Received',      'email',  1),
(1, 1, 'merchant_reg',  'Merchant Registration', 'email',  0),
(1, 1, 'billing',       'Billing Created',       'email',  1);

INSERT INTO settings (tenant_id, key, value, type, group_name) VALUES
(1, 'company_name',       'Company 2',              'string',  'general'),
(1, 'company_email',      'info@company2.sa',       'string',  'general'),
(1, 'company_phone',      '+966500000000',           'string',  'general'),
(1, 'company_address',    'Riyadh, Saudi Arabia',   'string',  'general'),
(1, 'company_website',    'https://company2.sa',    'string',  'general'),
(1, 'default_language',   'en',                     'string',  'general'),
(1, 'dark_mode_default',  'false',                  'boolean', 'appearance'),
(1, 'primary_color',      '#00aaff',                'string',  'appearance'),
(1, 'commission_rate',    '10',                     'integer', 'billing'),
(1, 'currency',           'SAR',                    'string',  'billing'),
(1, 'enable_2fa',         'false',                  'boolean', 'security'),
(1, 'session_timeout',    '60',                     'integer', 'security');
