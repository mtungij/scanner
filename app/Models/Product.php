<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'barcode',
        'price',
        'stock_quantity',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
