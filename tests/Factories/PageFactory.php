<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Portable\FilaCms\Models\Page;

class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $draft = fake()->numberBetween(0, 1);

        return [
            'title'     => fake()->words(15, true),
            'is_draft'  => $draft,
            'publish_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'expire_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'contents'  => json_decode('{"type": "doc", "content": [{"type": "paragraph", "attrs": {"class": null, "style": null, "textAlign": "start"}, "content": [{"text": "Lorem ipsum keme keme keme 48 years chaka biway chapter ano kemerloo at nang ng shontis at klapeypey-klapeypey katagalugan katagalugan nang sa at na ang jowabella buya daki nang makyonget biway chaka shongaers lorem ipsum keme keme chipipay intonses shontis at nang at bakit bella kasi lulu shonga-shonga lorem ipsum keme keme buya ano jutay ma-kyonget wasok otoko at bakit juts kirara at nang na ang shogal bella na ang kabog majubis jowabella at kabog bakit otoko at ang na ang 48 years at ang nakakalurky pranella shonga chopopo ng at chuckie na at nang bella kasi chopopo nang valaj ng shokot chipipay kasi tungril guash sangkatuts lulu ano majonders ganda lang kabog sa warla sa jutay biway at bakit matod majubis sa bonggakea jowabella oblation wiz shonga-shonga.", "type": "text"}]}]}', true),
        ];
    }
}
