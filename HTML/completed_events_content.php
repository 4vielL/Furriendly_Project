<?php
if (empty($completed_events)): ?>
    <div class="no-events">
        <div class="no-events-icon">✅</div>
        <h3>No Completed Events</h3>
        <p>You haven't completed any events yet. Events you attend will appear here.</p>
    </div>
<?php else: ?>
    <?php foreach ($completed_events as $event): ?>
    <div class="event-card">
        <div class="event-header">
            <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
            <span class="event-status status-completed">Completed</span>
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
                <div class="event-meta-item">
                    <strong>Host:</strong> <?php echo htmlspecialchars($event['host']); ?>
                </div>
            </div>
        </div>
        
        <div class="event-actions">
            <a href="event_details.php?id=2" class="action-btn view-btn">View Details</a>
            <button class="action-btn rate-btn">Rate Event</button>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>