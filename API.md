# API Documentation - System Kart Szkół Zrozum

## Overview

This document describes the API endpoints available in the Zrozum School Management System. All API endpoints require authentication via session cookies.

## Base URL

```
http://your-domain.com/api/
```

## Authentication

All API requests require a valid session. Users must be logged in to access any endpoint.

**Response on unauthorized access:**
```json
{
    "error": "Unauthorized"
}
```
Status Code: `401 Unauthorized`

---

## Endpoints

### 1. School Details API

**Endpoint:** `/api/school_detail.php`

Manages school detail records (custom fields for schools).

#### Get School Detail

**Method:** `GET`

**Parameters:**
- `id` (required) - Detail ID

**Example Request:**
```
GET /api/school_detail.php?id=5
```

**Success Response:**
```json
{
    "success": true,
    "data": {
        "id": 5,
        "school_id": 1,
        "section": "procedures",
        "field_name": "Procedury odbioru",
        "field_value": "Szczegółowy opis procedur...",
        "display_order": 1,
        "updated_at": "2024-09-15 10:30:00",
        "updated_by": 2
    }
}
```

**Error Response:**
```json
{
    "error": "Detail not found"
}
```
Status Code: `404 Not Found`

#### Update School Detail

**Method:** `PUT` (or `POST` with `_method=PUT`)

**Request Body:**
```json
{
    "id": 5,
    "value": "Updated value for the field"
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Detail updated successfully",
    "data": {
        "id": 5,
        "old_value": "Previous value",
        "new_value": "Updated value for the field"
    }
}
```

**Notes:**
- Automatically logs change to `change_history` table
- Updates `updated_at` timestamp and `updated_by` user ID

#### Create School Detail

**Method:** `POST`

**Request Body:**
```json
{
    "school_id": 1,
    "section": "custom_section",
    "field_name": "Field Name",
    "field_value": "Field value content"
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Detail added successfully",
    "data": {
        "id": 15
    }
}
```

---

### 2. Change History API

**Endpoint:** `/api/change_history.php`

Retrieves change history for school details.

#### Get Change History

**Method:** `GET`

**Parameters:**
- `detail_id` (required) - School detail ID

**Example Request:**
```
GET /api/change_history.php?detail_id=5
```

**Success Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "table_name": "school_details",
            "record_id": 5,
            "field_name": "Procedury odbioru",
            "old_value": "Old value",
            "new_value": "New value",
            "changed_by": 2,
            "changed_at": "2024-09-15 14:30:00",
            "username": "jan.kowalski"
        },
        {
            "id": 100,
            "table_name": "school_details",
            "record_id": 5,
            "field_name": "Procedury odbioru",
            "old_value": null,
            "new_value": "Old value",
            "changed_by": 1,
            "changed_at": "2024-08-20 10:00:00",
            "username": "admin"
        }
    ]
}
```

**Notes:**
- Returns up to 50 most recent changes
- Ordered by `changed_at` descending (newest first)
- Includes username of who made the change

---

### 3. User Preferences API

**Endpoint:** `/api/preferences.php`

Manages user preferences (e.g., visible sections in school card).

#### Save Preference

**Method:** `POST`

**Request Body:**
```json
{
    "key": "school_card_visible_sections",
    "value": ["basic_info", "contact", "events", "tasks"]
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Preference saved"
}
```

**Notes:**
- Arrays are automatically JSON encoded
- Uses `ON DUPLICATE KEY UPDATE` for upsert behavior

#### Get Preference

**Method:** `GET`

**Parameters:**
- `key` (required) - Preference key

**Example Request:**
```
GET /api/preferences.php?key=school_card_visible_sections
```

**Success Response:**
```json
{
    "success": true,
    "data": "[\"basic_info\",\"contact\",\"events\",\"tasks\"]"
}
```

**Notes:**
- Returns JSON-encoded string if value is an array
- Returns `null` if preference doesn't exist

---

## Common Preference Keys

### school_card_visible_sections

**Type:** Array of strings

**Description:** Controls which sections are visible on the school card page.

**Possible values:**
- `basic_info` - Basic information section
- `contact` - Contact information
- `schedule` - Schedule/timetable
- `procedures` - Procedures
- `afterschool` - After-school care
- `events` - Events list
- `contracts` - Contracts list
- `tasks` - Tasks list
- `notes` - Notes section

**Example:**
```json
["basic_info", "contact", "events", "tasks"]
```

---

## Error Codes

All API endpoints return standard HTTP status codes:

- `200 OK` - Request successful
- `400 Bad Request` - Invalid request parameters
- `401 Unauthorized` - Not authenticated
- `404 Not Found` - Resource not found
- `405 Method Not Allowed` - HTTP method not supported
- `500 Internal Server Error` - Server error

## Error Response Format

```json
{
    "error": "Error message description"
}
```

---

## Rate Limiting

Currently, there is no rate limiting implemented. Consider implementing rate limiting in production environments.

---

## CORS

CORS is not currently configured. If you need to access the API from a different domain, configure CORS headers appropriately.

---

## Future Endpoints (Planned)

### Task Management
- `POST /api/task.php` - Create task
- `PUT /api/task.php` - Update task
- `DELETE /api/task.php` - Delete task

### Event Management
- `POST /api/event.php` - Create event
- `PUT /api/event.php` - Update event
- `DELETE /api/event.php` - Delete event

### School Management
- `POST /api/school.php` - Create school
- `PUT /api/school.php` - Update school
- `GET /api/school.php` - Get school details

### File Upload
- `POST /api/upload.php` - Upload files
- `GET /api/files.php` - List uploaded files

### Import/Export
- `POST /api/import.php` - Import data from Excel/CSV
- `GET /api/export.php` - Export data (JSON format)

---

## Examples

### JavaScript Fetch Example

```javascript
// Get change history
async function getChangeHistory(detailId) {
    const response = await fetch(`/api/change_history.php?detail_id=${detailId}`);
    const data = await response.json();
    return data;
}

// Update school detail
async function updateSchoolDetail(detailId, newValue) {
    const response = await fetch('/api/school_detail.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: detailId,
            value: newValue
        })
    });
    const data = await response.json();
    return data;
}

// Save user preference
async function savePreference(key, value) {
    const response = await fetch('/api/preferences.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            key: key,
            value: value
        })
    });
    const data = await response.json();
    return data;
}
```

### PHP cURL Example

```php
<?php
// Update school detail
$ch = curl_init('http://your-domain.com/api/school_detail.php');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'id' => 5,
    'value' => 'New value'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$result = curl_exec($ch);
$data = json_decode($result, true);
curl_close($ch);

if ($data['success']) {
    echo "Updated successfully!";
}
?>
```

---

## Security Considerations

1. **Session-based authentication** - All requests must include valid session cookie
2. **SQL Injection Protection** - All queries use prepared statements
3. **XSS Protection** - Output is escaped when rendered
4. **CSRF Protection** - Consider implementing CSRF tokens for state-changing operations
5. **HTTPS** - Use HTTPS in production to encrypt data in transit

---

## Support

For API support, please contact the development team or open an issue on GitHub.

## Changelog

### Version 1.0.0 (2024)
- Initial API release
- School details CRUD operations
- Change history retrieval
- User preferences management
