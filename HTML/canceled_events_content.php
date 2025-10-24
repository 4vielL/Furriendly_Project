<?php
// Sample canceled events data
$canceled_events = []; // Empty array for demonstration

if (empty($canceled_events)): ?>
    <div class="no-events">
        <div class="no-events-icon">❌</div>
        <h3>No Canceled Events</h3>
        <p>You haven't canceled any event participations.</p>
    </div>
<?php else: ?>
    <?php foreach ($canceled_events as $event): ?>
    <div class="event-card">
        <div class="event-header">
            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
            <span class="event-status status-canceled">Canceled</span>
        </div>
        
        <div class="event-details">
            <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
            
            <div class="event-meta">
                <div class="event-meta-item">
                    <strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?>
                </div>
                <div class="event-meta-item">
                    <strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?>
                </div>
                <div class="event-meta-item">
                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>