<?php

if (session_status() === PHP_SESSION_NONE) 
{
    // Preview helpers rely on the session because admin preview mode is just another session state.
    session_start();
}

function isAdminPreviewMode(): bool
{
    // Admin preview means an admin is browsing the customer side without also having a customer session.
    return isset($_SESSION['admin_id']) && !isset($_SESSION['user_id']);
}

function renderAdminPreviewBanner(string $backHref = '/Team23_PixelPals_Term2_Final/public/admin/dashboard.php'): void
{
    // Most customer pages just need a simple banner and a route back to the dashboard.
    if (!isAdminPreviewMode()) {
        return;
    }

    echo '<div style="position:sticky;top:0;z-index:1000;width:min(1180px,calc(100% - 32px));margin:18px auto 0;padding:14px 18px;border-radius:22px;background:rgba(17,37,77,0.92);color:#fff;box-shadow:0 20px 60px rgba(17,37,77,0.18);display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;">';
    echo '<div><strong style="display:block;margin-bottom:4px;">Admin Preview Mode</strong></div>';
    echo '<a href="' . htmlspecialchars($backHref) . '" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 16px;border-radius:999px;background:linear-gradient(135deg,#ccff6f,#f5ff9a);color:#11254d;font-weight:800;text-decoration:none;white-space:nowrap;">Back to Admin Dashboard</a>';
    echo '</div>';
}

function renderAdminPreviewUnavailablePage
(
    string $title,
    string $message,
    string $backHref = '/Team23_PixelPals_Term2_Final/public/admin/dashboard.php'
): 
void 
{
    // Some customer-only pages do not make sense in preview mode, so render a lightweight fallback page instead.
    if (!isAdminPreviewMode()) {
        return;
    }

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>'
        . htmlspecialchars($title)
        . '</title></head><body style="margin:0;min-height:100vh;font-family:Verdana,Trebuchet MS,Segoe UI,sans-serif;background:linear-gradient(180deg,#81d4ff 0%,#b6d4ff 36%,#efe7ff 100%);color:#11254d;">';
    echo '<div style="width:min(760px,calc(100% - 32px));margin:48px auto;padding:32px;border-radius:30px;background:rgba(255,255,255,0.92);box-shadow:0 20px 60px rgba(17,37,77,0.18);border:1px solid rgba(255,255,255,0.7);">';
    echo '<div style="display:inline-flex;padding:8px 14px;border-radius:999px;background:linear-gradient(90deg,rgba(87,166,255,0.16),rgba(255,109,178,0.18));font-weight:800;letter-spacing:0.05em;text-transform:uppercase;font-size:0.8rem;">Admin Preview</div>';
    echo '<h1 style="margin:18px 0 12px;font-size:clamp(2rem,5vw,3.6rem);line-height:0.96;">' . htmlspecialchars($title) . '</h1>';
    echo '<p style="margin:0 0 22px;line-height:1.7;opacity:0.86;">' . htmlspecialchars($message) . '</p>';
    echo '<a href="' . htmlspecialchars($backHref) . '" style="display:inline-flex;align-items:center;justify-content:center;padding:13px 18px;border-radius:16px;background:linear-gradient(135deg,#ccff6f,#f5ff9a);color:#11254d;font-weight:800;text-decoration:none;">Back to Admin Dashboard</a>';
    echo '</div></body></html>';
    exit();
}
