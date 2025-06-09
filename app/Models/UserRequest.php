<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRequest extends Model
{
    use HasFactory;
    
    // Tentukan nama tabel yang sesuai dengan database
    protected $table = 'requests';
    
    protected $fillable = [
        'user_id',
        'service',
        'file',
        'status',
    ];
    
    // Tetapkan nilai default untuk status
    protected $attributes = [
        'status' => 'Menunggu',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}