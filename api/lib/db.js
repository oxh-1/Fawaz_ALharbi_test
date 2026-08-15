/**
 * Cloudflare D1 Database Helper Layer
 * Provides typed query helpers that wrap D1's prepare/bind/run/all/first APIs.
 * All methods accept `env.DB` as the D1 database binding from wrangler.toml.
 */

// ─────────────────────────────────────────────────────────────────────────────
// Core query helpers
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Run a SELECT query that returns multiple rows.
 * @param {D1Database} db
 * @param {string} sql
 * @param {Array} params
 * @returns {Promise<Array>}
 */
export async function queryAll(db, sql, params = []) {
  const stmt = db.prepare(sql);
  const bound = params.length > 0 ? stmt.bind(...params) : stmt;
  const result = await bound.all();
  return result.results || [];
}

/**
 * Run a SELECT query that returns a single row or null.
 * @param {D1Database} db
 * @param {string} sql
 * @param {Array} params
 * @returns {Promise<Object|null>}
 */
export async function queryFirst(db, sql, params = []) {
  const stmt = db.prepare(sql);
  const bound = params.length > 0 ? stmt.bind(...params) : stmt;
  return await bound.first() || null;
}

/**
 * Run an INSERT / UPDATE / DELETE query.
 * @param {D1Database} db
 * @param {string} sql
 * @param {Array} params
 * @returns {Promise<D1Result>}
 */
export async function queryRun(db, sql, params = []) {
  const stmt = db.prepare(sql);
  const bound = params.length > 0 ? stmt.bind(...params) : stmt;
  return await bound.run();
}

/**
 * Run an INSERT and return the last inserted row ID.
 * @param {D1Database} db
 * @param {string} sql
 * @param {Array} params
 * @returns {Promise<number>} lastRowId
 */
export async function queryInsert(db, sql, params = []) {
  const result = await queryRun(db, sql, params);
  return result.meta?.last_row_id ?? null;
}

// ─────────────────────────────────────────────────────────────────────────────
// Domain queries
// ─────────────────────────────────────────────────────────────────────────────

export const domainDb = {
  findByHost: (db, host) =>
    queryFirst(db,
      `SELECT d.*, t.id AS t_id, t.name AS t_name, t.slug AS t_slug, t.status AS t_status
       FROM domains d LEFT JOIN tenants t ON t.id = d.tenant_id
       WHERE d.domain = ? AND d.status = 'active' LIMIT 1`,
      [host]
    ),
  list: (db) =>
    queryAll(db,
      `SELECT d.*, t.name AS tenant_name, t.slug AS tenant_slug
       FROM domains d LEFT JOIN tenants t ON t.id = d.tenant_id
       ORDER BY d.created_at DESC`
    ),
  create: (db, domain, tenantId, type) =>
    queryInsert(db,
      `INSERT INTO domains (domain, tenant_id, type, status, created_at, updated_at)
       VALUES (?, ?, ?, 'active', datetime('now'), datetime('now'))`,
      [domain, tenantId, type]
    ),
  delete: (db, id) =>
    queryRun(db, `DELETE FROM domains WHERE id = ?`, [id]),
};

// ─────────────────────────────────────────────────────────────────────────────
// User queries
// ─────────────────────────────────────────────────────────────────────────────

export const userDb = {
  findByEmail: (db, email) =>
    queryFirst(db, `SELECT * FROM users WHERE email = ? LIMIT 1`, [email]),

  findById: (db, id) =>
    queryFirst(db, `SELECT * FROM users WHERE id = ? LIMIT 1`, [id]),

  findByGoogleId: (db, googleId) =>
    queryFirst(db, `SELECT * FROM users WHERE google_id = ? LIMIT 1`, [googleId]),

  create: (db, { name, email, passwordHash, salt, googleId, picture, tenantId, isAdmin }) =>
    queryInsert(db,
      `INSERT INTO users (name, email, password, google_id, picture, tenant_id, is_super_admin, status, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, 'active', datetime('now'), datetime('now'))`,
      [name, email, passwordHash || null, googleId || null, picture || null, tenantId || 1, isAdmin ? 1 : 0]
    ),

  updateLastLogin: (db, id) =>
    queryRun(db, `UPDATE users SET last_login_at = datetime('now'), updated_at = datetime('now') WHERE id = ?`, [id]),

  updateGoogleInfo: (db, id, googleId, picture) =>
    queryRun(db,
      `UPDATE users SET google_id = ?, picture = ?, updated_at = datetime('now') WHERE id = ?`,
      [googleId, picture, id]
    ),

  listAll: (db, search, status, limit = 50, offset = 0) => {
    const params = [];
    let where = 'WHERE 1=1';
    if (search) {
      where += ' AND (u.name LIKE ? OR u.email LIKE ?)';
      params.push(`%${search}%`, `%${search}%`);
    }
    if (status) {
      where += ' AND u.status = ?';
      params.push(status);
    }
    params.push(limit, offset);
    return queryAll(db,
      `SELECT u.id, u.name, u.email, u.status, u.is_super_admin, u.tenant_id,
              u.last_login_at, u.created_at, u.picture,
              (SELECT ip_address FROM audit_logs
               WHERE user_id = u.id AND action = 'login'
               ORDER BY created_at DESC LIMIT 1) AS last_login_ip,
              (SELECT created_at FROM audit_logs
               WHERE user_id = u.id AND action = 'login'
               ORDER BY created_at DESC LIMIT 1) AS last_login_log_at
       FROM users u
       ${where}
       ORDER BY u.created_at DESC
       LIMIT ? OFFSET ?`,
      params
    );
  },

  update: (db, id, fields) => {
    const keys = Object.keys(fields);
    const setClause = keys.map(k => `${k} = ?`).join(', ');
    const values = Object.values(fields);
    return queryRun(db, `UPDATE users SET ${setClause}, updated_at = datetime('now') WHERE id = ?`, [...values, id]);
  },

  delete: (db, id) =>
    queryRun(db, `DELETE FROM users WHERE id = ?`, [id]),

  count: (db) =>
    queryFirst(db, `SELECT COUNT(*) AS cnt FROM users`),
};

// ─────────────────────────────────────────────────────────────────────────────
// Audit Log queries
// ─────────────────────────────────────────────────────────────────────────────

export const auditDb = {
  create: (db, { userId, tenantId, action, model, modelId, oldData, newData, ipAddress, userAgent }) =>
    queryInsert(db,
      `INSERT INTO audit_logs (user_id, tenant_id, action, model, model_id, old_data, new_data, ip_address, user_agent, created_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))`,
      [
        userId || null,
        tenantId || null,
        action,
        model || null,
        modelId || null,
        oldData ? JSON.stringify(oldData) : null,
        newData ? JSON.stringify(newData) : null,
        ipAddress || null,
        userAgent || null,
      ]
    ),

  list: (db, { action, model, userId, limit = 50, offset = 0 } = {}) => {
    const params = [];
    let where = 'WHERE 1=1';
    if (action) { where += ' AND a.action = ?'; params.push(action); }
    if (model)  { where += ' AND a.model = ?';  params.push(model); }
    if (userId) { where += ' AND a.user_id = ?'; params.push(userId); }
    params.push(limit, offset);
    return queryAll(db,
      `SELECT a.*, u.name AS user_name, u.email AS user_email
       FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id
       ${where}
       ORDER BY a.created_at DESC LIMIT ? OFFSET ?`,
      params
    );
  },
};

// ─────────────────────────────────────────────────────────────────────────────
// Contact Messages queries
// ─────────────────────────────────────────────────────────────────────────────

export const contactDb = {
  create: (db, { tenantId, name, email, subject, message, ipAddress }) =>
    queryInsert(db,
      `INSERT INTO contact_messages (tenant_id, name, email, subject, message, ip_address, is_read, created_at)
       VALUES (?, ?, ?, ?, ?, ?, 0, datetime('now'))`,
      [tenantId || 1, name, email, subject, message, ipAddress || null]
    ),

  list: (db, tenantId) =>
    queryAll(db,
      `SELECT * FROM contact_messages WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 100`,
      [tenantId || 1]
    ),

  findById: (db, id) =>
    queryFirst(db, `SELECT * FROM contact_messages WHERE id = ? LIMIT 1`, [id]),

  markRead: (db, id, readBy) =>
    queryRun(db,
      `UPDATE contact_messages SET is_read = 1, read_by = ?, read_at = datetime('now') WHERE id = ?`,
      [readBy, id]
    ),

  delete: (db, id) =>
    queryRun(db, `DELETE FROM contact_messages WHERE id = ?`, [id]),
};

// ─────────────────────────────────────────────────────────────────────────────
// Stats queries
// ─────────────────────────────────────────────────────────────────────────────

export const statsDb = {
  global: async (db) => {
    const [users, merchants, bookings, reviews, settlements, activeMerchants, todayBookings] = await Promise.all([
      queryFirst(db, `SELECT COUNT(*) AS cnt FROM users`),
      queryFirst(db, `SELECT COUNT(*) AS cnt FROM merchants`),
      queryFirst(db, `SELECT COUNT(*) AS cnt FROM bookings`),
      queryFirst(db, `SELECT COUNT(*) AS cnt FROM reviews WHERE status = 'pending'`),
      queryFirst(db, `SELECT COALESCE(SUM(amount), 0) AS total FROM settlements`),
      queryFirst(db, `SELECT COUNT(*) AS cnt FROM merchants WHERE status = 'active'`),
      queryFirst(db, `SELECT COUNT(*) AS cnt FROM bookings WHERE date(booking_date) = date('now')`),
    ]);
    return {
      total_users:       users?.cnt || 0,
      total_merchants:   merchants?.cnt || 0,
      total_bookings:    bookings?.cnt || 0,
      pending_reviews:   reviews?.cnt || 0,
      total_settlements: settlements?.total || 0,
      active_merchants:  activeMerchants?.cnt || 0,
      today_bookings:    todayBookings?.cnt || 0,
    };
  },
};
