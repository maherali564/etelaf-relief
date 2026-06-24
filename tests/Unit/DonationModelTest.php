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

}
