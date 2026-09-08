<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Notifications\WelcomeNotification;
use Portable\FilaCms\Tests\TestCase;

/**
 * Covers the two league/commonmark code paths the package actually uses: markdown mailables and
 * Str::inlineMarkdown(). The notification tests all run under Notification::fake(), so they assert
 * a notification was queued without ever rendering it — nothing else converts markdown.
 */
class MarkdownRenderingTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_markdown_mailable_renders_to_html(): void
    {
        $user = config('auth.providers.users.model')::factory()->create(['name' => 'Ada']);

        $html = (string) (new WelcomeNotification($user))->toMail($user)->render();

        // Heading and button link come from commonmark, not blade.
        $this->assertStringContainsString('Welcome Ada', $html);
        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('View profile', $html);
    }

    public function test_inline_markdown_renders_emphasis(): void
    {
        // The string form used in AbstractContentResource's robots-meta description.
        $html = (string) str('set the robots meta tag to **No Index**.')->inlineMarkdown();

        $this->assertStringContainsString('<strong>No Index</strong>', $html);
        // inlineMarkdown must not wrap in a block element.
        $this->assertStringNotContainsString('<p>', $html);
    }
}
