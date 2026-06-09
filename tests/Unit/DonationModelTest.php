<?php

namespace Tests\Unit;

use App\Models\Donation;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DonationModelTest extends TestCase
{
    #[Test]
    public function it_has_fillable_attributes()
    {
        $donation = new Donation();
        $this->assertNotEmpty($donation->getFillable());
    }

    #[Test]
    public function it_uses_soft_deletes()
    {
        $uses = class_uses(Donation::class);
        $this->assertArrayHasKey(\Illuminate\Database\Eloquent\SoftDeletes::class, $uses);
    }
}
