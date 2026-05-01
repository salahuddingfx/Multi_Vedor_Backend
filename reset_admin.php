<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('role', 'admin')->first();
if ($user) {
    $user->password = Hash::make('admin123');
    $user->save();
    echo "Password reset for: " . $user->email . "\n";
} else {
    echo "No admin user found.\n";
}
