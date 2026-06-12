@include('errors.layout', [
    'code' => '500',
    'title' => 'Server error',
    'message' => 'Something went wrong while processing your request. Please try again or contact IT support.',
])