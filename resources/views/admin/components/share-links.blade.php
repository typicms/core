@props(['model'])

<nav class="share-links" aria-labelledby="share-links-heading">
    <span id="share-links-heading">{{ __('Share on:') }}</span>
    <ul class="share-links-list">
        <li class="share-links-item">
            <a class="share-links-link share-links-linkedin" href="https://www.linkedin.com/sharing/share-offsite/?url={{ url()->current() }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on LinkedIn') }} ({{ __('opens in a new tab') }})">
                <span class="share-links-icon share-links-linkedin-icon" aria-hidden="true"></span>
            </a>
        </li>
        <li class="share-links-item">
            <a class="share-links-link share-links-bluesky" href="https://bsky.app/intent/compose?text={{ url()->current() }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on Bluesky') }} ({{ __('opens in a new tab') }})">
                <span class="share-links-icon share-links-bluesky-icon" aria-hidden="true"></span>
            </a>
        </li>
        <li class="share-links-item">
            <a class="share-links-link share-links-x" href="https://x.com/intent/tweet?text={{ rawurlencode($model->title) }}&url={{ url()->current() }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on X') }} ({{ __('opens in a new tab') }})">
                <span class="share-links-icon share-links-x-icon" aria-hidden="true"></span>
            </a>
        </li>
        <li class="share-links-item">
            <a class="share-links-link share-links-facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on Facebook') }} ({{ __('opens in a new tab') }})">
                <span class="share-links-icon share-links-facebook-icon" aria-hidden="true"></span>
            </a>
        </li>
        <li class="share-links-item">
            <a class="share-links-link share-links-whatsapp" href="https://wa.me/?text={{ url()->current() }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share via WhatsApp') }} ({{ __('opens in a new tab') }})">
                <span class="share-links-icon share-links-whatsapp-icon" aria-hidden="true"></span>
            </a>
        </li>
        <li class="share-links-item">
            <a class="share-links-link share-links-mail" href="mailto:?subject={{ rawurlencode($model->title) }}&body={{ url()->current() }}" aria-label="{{ __('Share via email') }}">
                <span class="share-links-icon share-links-mail-icon" aria-hidden="true"></span>
            </a>
        </li>
    </ul>
</nav>
