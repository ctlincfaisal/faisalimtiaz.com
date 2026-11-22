New project enquiry

Name: {{ $inquiry['name'] }}
Email: {{ $inquiry['email'] }}
Project type: {{ $inquiry['project_type'] ?? 'Not specified' }}
Approximate budget: {{ $inquiry['budget'] ?? 'Not specified' }}
Submitted: {{ $inquiry['submitted_at'] }}

Project details:
{{ $inquiry['details'] }}
