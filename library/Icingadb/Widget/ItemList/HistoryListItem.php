<?php

namespace Icinga\Module\Icingadb\Widget\ItemList;

use Icinga\Module\Icingadb\Common\HostLink;
use Icinga\Module\Icingadb\Common\HostStates;
use Icinga\Module\Icingadb\Common\Icons;
use Icinga\Module\Icingadb\Common\ServiceLink;
use Icinga\Module\Icingadb\Common\ServiceStates;
use Icinga\Module\Icingadb\Widget\CheckAttempt;
use Icinga\Module\Icingadb\Widget\CommonListItem;
use Icinga\Module\Icingadb\Widget\StateChange;
use Icinga\Module\Icingadb\Widget\TimeAgo;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Web\Widget\Icon;

class HistoryListItem extends CommonListItem
{
    use HostLink;
    use ServiceLink;

    protected function assembleCaption(BaseHtmlElement $caption)
    {
        switch ($this->item->event_type) {
            case 'comment_add':
            case 'comment_remove':
                $caption->add([
                    New Icon(Icons::USER),
                    $this->item->comment->author,
                    ': ',
                    $this->item->comment->comment
                ]);

                break;
            case 'downtime_end':
            case 'downtime_start':
                $caption->add([
                    New Icon(Icons::USER),
                    $this->item->downtime->author,
                    ': ',
                    $this->item->downtime->comment
                ]);

                break;
            case 'notification':
                $caption->add($this->item->notification->text);

                if (! empty($this->item->notification->author)) {
                    $caption->prepend([
                        New Icon(Icons::USER),
                        $this->item->notification->author,
                        ': '
                    ]);
                }

                break;
            case 'state_change':
                $caption
                    ->add($this->item->state->output)
                    ->getAttributes()
                        ->add('class', 'plugin-output');

                break;
        }
    }

    protected function assembleVisual(BaseHtmlElement $visual)
    {
        switch ($this->item->event_type) {
            case 'comment_add':
                $visual->add(
                    Html::tag('div', ['class' => 'icon-ball ball-size-xl'], new Icon(Icons::COMMENT))
                );

                break;
            case 'comment_remove':
                $visual->add(
                    Html::tag('div', ['class' => 'icon-ball ball-size-xl'], new Icon(Icons::REMOVE))
                );

                break;
            case 'downtime_end':
                $visual->add(
                    Html::tag('div', ['class' => 'icon-ball ball-size-xl'], new Icon(Icons::REMOVE))
                );

                break;
            case 'downtime_start':
                $visual->add(
                    Html::tag('div', ['class' => 'icon-ball ball-size-xl'], new Icon(Icons::IN_DOWNTIME))
                );

                break;
            case 'notification':
                $visual->add(
                    Html::tag('div', ['class' => 'icon-ball ball-size-xl'], new Icon(Icons::NOTIFICATION))
                );

                break;
            case 'state_change':
                $previousState = 'previous_soft_state';

                if ($this->item->state->state_type === 'soft') {
                    $state = 'soft_state';

                    $visual->add(new CheckAttempt($this->item->state->attempt, $this->item->state->max_check_attempts));
                } else {
                    $state = 'hard_state';
                }

                if ($this->item->object_type === 'host') {
                    $state = HostStates::text($this->item->state->$state);
                    $previousHardState = HostStates::text($this->item->state->$previousState);
                } else {
                    $state = ServiceStates::text($this->item->state->$state);
                    $previousHardState = ServiceStates::text($this->item->state->$previousState);
                }

                $visual->prepend(new StateChange($state, $previousHardState));

                break;
        }
    }

    protected function assembleTitle(BaseHtmlElement $title)
    {
        switch ($this->item->event_type) {
            case 'comment_add':
                $title->add('Comment added');

                break;
            case 'comment_remove':
                $title->add('Comment removed');

                break;
            case 'downtime_end':
                $title->add('Downtime ended');

                break;
            case 'downtime_start':
                $title->add('Downtime started');

                break;
            case 'notification':
                $title->add([
                    'Notification',
                    ': ',
                    sprintf(
                        NotificationListItem::PHRASES[$this->item->notification->type],
                        ucfirst($this->item->object_type)
                    )
                ]);

                break;
            case 'state_change':
                $title->add(sprintf('%s state changed', ucfirst($this->item->state->state_type)));

                break;
            default:
                $title->add($this->item->event_type);

                break;
        }

        if ($this->item->object_type === 'host') {
            $link = $this->createHostLink($this->item->host, true);
        } else {
            $link = $this->createServiceLink($this->item->service, $this->item->service->host, true);
        }

        $title->add([Html::tag('br'), $link]);
    }

    protected function createTimestamp()
    {
        return new TimeAgo($this->item->event_time);
    }
}