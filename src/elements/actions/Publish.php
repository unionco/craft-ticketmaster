<?php
/**
 * Ticketmaster plugin for Craft CMS 4.x
 *
 * Ticket master ticket feed for venues.
 *
 * @link      https://github.com/unionco
 * @copyright Copyright (c) 2019 Union
 */

namespace unionco\ticketmaster\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use unionco\ticketmaster\Ticketmaster;

/**
 * EventQuery represents a SELECT SQL statement for events in a way that is independent of DBMS.
 *
 */
class Publish extends ElementAction
{
    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $confirmationMessage;

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('ticketmaster', 'Publish');
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        /** @var Element[] $elements */
        $elements = $query->all();
        $successCount = 0;
        $failCount = 0;

        $this->_publishElements($elements, $successCount, $failCount);

        // Did all of them fail?
        if ($successCount === 0) {
            $this->setMessage(Craft::t('ticketmaster', 'Could not published events due to validation errors.'));
            return false;
        }

        if ($failCount !== 0) {
            $this->setMessage(Craft::t('ticketmaster', 'Could not publish all events due to validation errors.'));
        } else {
            $this->setMessage(Craft::t('ticketmaster', 'Events published.'));
        }

        return true;
    }

    /**
     *
     */
    private function _publishElements(array $elements, int &$successCount, int &$failCount)
    {
        $elementService = Craft::$app->getElements();

        foreach ($elements as $element) {
            try {
                $result = Ticketmaster::$plugin->elements->publishEvent($element);
                if (!$result) {
                    throw new Exception("Error publishing event", 1);
                }
                $element->isDirty = false;
                $element->isPublished = true;
                $elementService->saveElement($element);
            } catch (\Throwable $th) {
                $failCount++;
                continue;
            }

            $successCount++;
        }
    }
}
