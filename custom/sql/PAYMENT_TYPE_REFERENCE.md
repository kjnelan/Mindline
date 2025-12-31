# Payment Type Reference

## Database Details

### List: `payment_type`
This list is stored in the `list_options` table.

### Default OpenEMR Options:
```sql
-- Default options in OpenEMR 7.0.4
('payment_type', 'insurance', 'Insurance', 10, 0)
('payment_type', 'patient', 'Patient', 20, 0)
```

### Custom Option for Self-Pay:
```sql
-- Custom option for your mental health practice
('payment_type', 'client', 'Self-Pay (Client)', 30, 0)
```

## How It's Used

### In Patient Data:
- Field: `patient_data.userlist1` (in database)
- Maps to: `payment_type` (in API)
- Values: 'insurance', 'patient', or 'client'

### In Insurance Tab:
The InsuranceTab.jsx checks:
```javascript
const isSelfPay = patient.payment_type === 'client';
```

If `payment_type === 'client'`:
- Shows "Self-Pay (Client)" badge in orange
- Hides insurance sections by default
- Shows "Show Insurance Records" toggle button
- Allows viewing/editing insurance even for self-pay (for future coverage, etc.)

## To Add the 'client' Option:

Run the SQL file: `custom/sql/add_client_payment_type.sql`

This will:
1. Add 'client' as an option in the payment_type list
2. Set the title to "Self-Pay (Client)"
3. Make it active and available for selection

## Where to Set Payment Type:

In OpenEMR's **Demographics** page (the old one, not your custom one):
1. Go to Patient Demographics
2. Look for a field that uses the "Payment Type" list
3. Select "Self-Pay (Client)" for self-pay patients

OR you can add it to your custom Demographics tab if you want to edit it there.
