<?php 
    $content = $mysqli->prepare("SELECT * FROM `events` WHERE id = ?");
    $content->bind_param('i', $contentId);
    $content->execute();
    $contentResult = $content->get_result();

    if($contentResult->num_rows <= 0) {
        http_response_code(404);
        include_once($_SERVER['DOCUMENT_ROOT'] . ROOT_DIR . '404.php');
        exit();
    }

    $content = $contentResult->fetch_assoc();

    $title = (!empty($content['meta_title']) ? $content['meta_title'] : $content['name']);
    $description = (!empty($content['meta_description']) ? $content['meta_description'] : $content['excerpt']);
    $author = (!empty($content['meta_author']) ? $content['meta_author'] : $content['author']);
    $keywords = (!empty($content['meta_keywords']) ? $content['meta_keywords'] : '');

    require_once(dirname(__DIR__, 2) . '/header.php');

    echo carousel($content['id'], false, '', 'events');
    $breadcrumbs = new breadcrumbs($content['id']); echo $breadcrumbs->display();

    $startDate = date('Y-m-d', strtotime($content['start_date']));
    $endDate = date('Y-m-d', strtotime($content['end_date']));
    $startTime = date('H:i', strtotime($content['start_date']));
    $endTime = date('H:i', strtotime($content['end_date']));

    if($startDate < $endDate) {
        $eventDate = date('d/m/Y', strtotime($content['start_date'])) . ' to ' . date('d/m/Y', strtotime($content['end_date']));
        $eventDuration = ((strtotime($endDate) - strtotime($startDate)) / 60 / 60 / 24) + 1 . ' Days';
    }
    else {
        $eventDate = date('d/m/Y', strtotime($content['start_date']));
        $eventDuration = '1 Day';
    }

    if($startTime < $endTime) {
        $eventTime = $startTime . ' to ' . $endTime;
    }
    else {
        $eventTime = $startTime;
    }
?>

<div class="row eventPage">
    <div class="uc content col order-1">
        <?php parsecontent($content['content']); ?>
    </div>

    <div class="eventSidebar col-lg-4 order-0 order-lg-2 mb-3 mb-lg-3">
        <?php if(!empty($content['featured_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . ROOT_DIR . $content['featured_image'])) : ?>
            <img src="<?php echo $content['featured_image']; ?>" class="featuredImage img-fluid mb-3">
        <?php endif; ?>

        <div class="bg-primary p-3">
            <ul class="list-group">
                <?php echo (!empty($content['author']) ? '<li class="list-group-item"><strong>Event Coordinator</strong> ' . $content['author'] . '</li>' : ''); ?>
                <?php echo (!empty($eventDate) ? '<li class="list-group-item"><strong>Date</strong> ' . $eventDate . '</li>' : ''); ?>
                <?php echo (!empty($eventTime) ? '<li class="list-group-item"><strong>Time</strong> ' . $eventTime . '</li>' : ''); ?>
                <?php echo (!empty($eventDuration) ? '<li class="list-group-item"><strong>Duration</strong> ' . $eventDuration . '</li>' : ''); ?>
            </ul>
        </div>
    </div>
</div>


<?php require_once(dirname(__DIR__, 2) . '/footer.php'); ?>