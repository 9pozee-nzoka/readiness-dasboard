# HOD Comment Directing Feature

## Overview

HODs now have the ability to **choose who receives their comments** instead of always defaulting to the Director. This provides flexibility in communication while maintaining proper hierarchy.

## Feature for HODs

When an HOD writes a comment, they can select who should receive it from a dropdown menu:

### Options Available:

1. **Auto (Director by default)** 🔄
   - Default hierarchical routing
   - Comment goes to Director
   - Maintains chain of command

2. **🎯 Director**
   - Explicitly direct to Director
   - For escalations and approvals
   - High-level strategic discussions

3. **👥 My Department Staff**
   - Broadcast to all staff in department
   - For department-wide instructions
   - Team coordination

4. **👤 Specific Person**
   - Choose any user in the system
   - Direct 1-on-1 communication
   - Bypasses hierarchy when needed
   - Shows dropdown of all users with their roles

## How It Works

### For HODs:
When commenting on a requirement, you'll see:
```
┌─────────────────────────────────────┐
│ Direct this comment to:             │
│ ┌───────────────────────────────┐   │
│ │ Auto (Director by default)    │   │
│ │ 🎯 Director                   │   │
│ │ 👥 My Department Staff        │   │
│ │ 👤 Specific Person           │   │
│ └───────────────────────────────┘   │
│                                     │
│ [If "Specific Person" selected]     │
│ ┌───────────────────────────────┐   │
│ │ Select person...              │   │
│ │ John Doe (employee)           │   │
│ │ Jane Smith (hod)              │   │
│ │ Director Name (director)      │   │
│ └───────────────────────────────┘   │
└─────────────────────────────────────┘
```

### For Other Roles:
- **Staff**: No selector (comments go to HOD automatically)
- **Director**: No selector (comments go to relevant HOD automatically)  
- **Admin**: No selector (comments go to both HOD and Director)

## Use Cases

### 1. Escalate to Director (Default)
**Scenario:** Budget approval needed
```
HOD: Selects "Auto" or "Director"
Director: Receives notification
Purpose: Official escalation
```

### 2. Direct to Department Staff
**Scenario:** Team instructions or clarifications
```
HOD: Selects "My Department Staff"
All ICT Staff: Receive notification
Purpose: Team-wide communication
```

### 3. Direct to Specific Person
**Scenario:** Follow up with specific staff member
```
HOD: Selects "Specific Person" → "Paul Nzoka"
Paul: Receives notification
Purpose: Targeted communication
```

## Notification Messages

The notification message adapts based on direction:

### To Director:
> "HOD Sarah Achieng from ICT commented on: Setup network infrastructure"

### To Department Staff:
> "Sarah Achieng commented on: Setup network infrastructure"  
> Notification title: "📢 Comment to Department"

### To Specific User:
> "Sarah Achieng directed a comment to you: Setup network infrastructure"  
> Notification title: "🎯 Comment Directed to You"

## Database Fields

Two new fields in `comments` table:
- `directed_to` - enum: 'auto', 'director', 'staff', 'specific_user'
- `directed_to_user_id` - foreign key to users table (for specific_user)

## Benefits

### ✅ Flexibility
- HODs aren't limited to one communication path
- Can reach anyone when needed

### ✅ Maintains Hierarchy
- Default behavior still follows chain of command
- Explicit choice required to deviate

### ✅ Better Communication
- Department-wide announcements
- Targeted follow-ups
- Direct Director escalation

### ✅ Clear Intent
- Recipients know comment was directed specifically to them
- Notification messages show direction
- Reduces ambiguity

## Implementation Details

### Form (Only for HODs):
```html
<select name="directed_to">
    <option value="auto">Auto (Director by default)</option>
    <option value="director">🎯 Director</option>
    <option value="staff">👥 My Department Staff</option>
    <option value="specific_user">👤 Specific Person</option>
</select>

<!-- Shows only if "specific_user" selected -->
<select name="directed_to_user_id">
    <option value="">Select person...</option>
    <option value="1">John Doe (employee)</option>
    <!-- ... more users ... -->
</select>
```

### Backend Logic:
```php
if ($comment->directed_to === 'specific_user') {
    // Notify specific user
    notifySpecificUser($comment, $requirement);
} elseif ($comment->directed_to === 'director') {
    // Notify director
    notifyDirector($comment, $requirement);
} elseif ($comment->directed_to === 'staff') {
    // Notify department staff
    notifyDepartmentStaff($comment, $requirement, $department);
} else {
    // Auto (default hierarchical)
    notifyDirector($comment, $requirement);
}
```

## Examples

### Example 1: HOD Clarifying with Staff
```
1. HOD writes comment about requirement
2. Selects "My Department Staff"
3. All ICT staff receive notification
4. Staff can reply back to HOD
```

### Example 2: HOD Escalating to Director
```
1. HOD writes comment about budget issue
2. Selects "Director" (or leaves as "Auto")
3. Director receives notification
4. Director can provide guidance
```

### Example 3: HOD Following Up with Specific Staff
```
1. HOD writes comment about overdue task
2. Selects "Specific Person" → "Paul Nzoka"
3. Only Paul receives notification
4. Targeted follow-up without CCing everyone
```

## Testing

### Test as HOD:
1. Log in as HOD
2. Navigate to any requirement
3. Click "Comment"
4. See recipient dropdown
5. Try each option and verify notifications

### Verify Notifications:
- **Director option**: Director gets notified
- **Staff option**: All department members get notified
- **Specific person**: Only that person gets notified
- **Auto**: Works like director option

## Future Enhancements

Possible additions:
- Director choosing recipients (to multiple HODs)
- Staff choosing between HOD and specific colleagues
- Group selections (multiple specific people)
- Reply-all functionality

## Summary

| Who | Can Direct To |
|-----|---------------|
| **HOD** | Auto (Director), Director, Department Staff, Specific Person |
| **Staff** | HOD (automatic, no choice) |
| **Director** | Relevant HOD (automatic, no choice) |
| **Admin** | HOD + Director (automatic, no choice) |

This feature gives HODs the flexibility they need while keeping the system organized and hierarchical by default.
