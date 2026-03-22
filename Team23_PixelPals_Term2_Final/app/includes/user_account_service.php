<?php

function update_user_profile(PDO $db, int $userId, string $username, string $firstName, string $lastName, string $email, ?string $dateOfBirth): void
{
    // Both self-service account edits and admin customer edits reuse this one update helper.
    $stmt = $db->prepare(
        'UPDATE users
         SET Username = ?, FirstName = ?, LastName = ?, Email = ?, DateOfBirth = ?
         WHERE UserID = ?'
    );
    $stmt->execute([$username, $firstName, $lastName, $email, $dateOfBirth, $userId]);
}

function delete_user_with_relations(PDO $db, int $userId): void
{
    // Account deletion touches several related tables, so keep it wrapped in one transaction.
    $db->beginTransaction();

    try {
        // Remove basket contents first because they depend on the user's basket row.
        $deleteBasketItems = $db->prepare(
            'DELETE bi
             FROM basketitem bi
             INNER JOIN basket b ON bi.BasketID = b.BasketID
             WHERE b.UserID = ?'
        );
        $deleteBasketItems->execute([$userId]);

        $deleteBaskets = $db->prepare('DELETE FROM basket WHERE UserID = ?');
        $deleteBaskets->execute([$userId]);

        // Order items have to go before the parent orders because of foreign key links.
        $deleteOrderItems = $db->prepare(
            'DELETE oi
             FROM orderitem oi
             INNER JOIN orders o ON oi.OrderID = o.OrderID
             WHERE o.UserID = ?'
        );
        $deleteOrderItems->execute([$userId]);

        $deleteOrders = $db->prepare('DELETE FROM orders WHERE UserID = ?');
        $deleteOrders->execute([$userId]);

        $deleteReviews = $db->prepare('DELETE FROM reviews WHERE UserID = ?');
        $deleteReviews->execute([$userId]);

        $deleteUser = $db->prepare('DELETE FROM users WHERE UserID = ?');
        $deleteUser->execute([$userId]);

        // Only commit once every dependent delete has succeeded.
        $db->commit();
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        // Bubble the error back up so the calling action can decide what flash message to show.
        throw $exception;
    }
}
