<?php

namespace Domain\Webhook\Bags;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Psy\Util\Json;

/**
 * Class UserBag
 * @package Domain\User\Bags
 * @property string actor
 * @property string repository
 * @property string full_name
 * @property string type
 * @property string title
 * @property string branch_name
 * @property string summary
 * @property string source
 * @property string destination
 * @property string approval
 * @property string state
 * @property string comment
 */
class DiscordBag
{
    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function jsonAttributes()
    {
        return (is_array($this->attributes)) ? json_encode($this->attributes) : null;
    }

    public static function fromRequest($attributes, $request = null)
    {
        $data = [];

        $event = $request ? $request->header('X-Event-Key', '') : '';
        $data['event'] = $event;

        //repo:push
        if (isset($attributes['push'])) {
            $data['type'] = 'push';

            $data['title'] = (isset($attributes['push']['changes'][0]['old']['target']['hash'])) ?
                $attributes['push']['changes'][0]['old']['target']['hash'] : null;

            $data['branch_name'] = (isset($attributes['push']['changes'][0]['old']['name'])) ?
                $attributes['push']['changes'][0]['old']['name'] : null;

            $data['summary'] = (isset($attributes['push']['changes'][0]['old']['target']['summary']['raw'])) ?
                $attributes['push']['changes'][0]['old']['target']['summary']['raw'] : null;

            $data['actor'] = (isset($attributes['actor']['display_name'])) ?
                $attributes['actor']['display_name'] : null;

            $data['repository'] = (isset($attributes['repository']['full_name'])) ?
                $attributes['repository']['full_name'] : null;
        }

        //pullrequest:updated | pullrequest:created | pullrequest:fulfilled | pullrequest:comment_created
        if (isset($attributes['pullrequest'])) {
            $data['type'] = 'pullrequest';

            $data['title'] = sprintf('#%d %s', $attributes['pullrequest']['id'], $attributes['pullrequest']['title']);

            $data['source'] = (isset($attributes['pullrequest']['source']['branch']['name'])) ?
                $attributes['pullrequest']['source']['branch']['name'] : null;

            $data['destination'] = (isset($attributes['pullrequest']['destination']['branch']['name'])) ?
                $attributes['pullrequest']['destination']['branch']['name'] : null;

            $data['summary'] = (isset($attributes['pullrequest']['description'])) ?
                $attributes['pullrequest']['description'] : null;

            $data['repository'] = (isset($attributes['repository']['full_name'])) ?
                $attributes['repository']['full_name'] : null;

            $data['actor'] = (isset($attributes['actor']['display_name'])) ?
                $attributes['actor']['display_name'] : null;

            // pullrequest:approved
            if (isset($attributes['approval'])) {
                $data['approval'] = (isset($attributes['approval']['user']['display_name'])) ?
                    $attributes['approval']['user']['display_name'] : null;
            }

            // pullrequest:comment_created
            if (isset($attributes['comment'])) {

                $data['comment'] = ((isset($attributes['comment']['user']['display_name'])) ?
                    sprintf('%s', $attributes['comment']['user']['display_name']) : '')
                    .
                    (isset($attributes['comment']['inline']) ?
                    sprintf(' (file: %s, lines: %s)',
                      $attributes['comment']['inline']['path'],
                      ($attributes['comment']['inline']['from'] ? $attributes['comment']['inline']['from'] . '-' : '')
                      . $attributes['comment']['inline']['to'])
                    : '')
                    .
                    (isset($attributes['comment']['content']['raw']) ?
                    ': ' . $attributes['comment']['content']['raw'] : '')
                    ;
            }
        }

        //repo:commit_status_updated
        //repo:commit_status_created

        if (isset($attributes['commit_status'])) {
            $data['type'] = 'commit_status';

            $data['actor'] = (isset($attributes['actor']['display_name'])) ?
                $attributes['actor']['display_name'] : null;

            $data['state'] = (isset($attributes['commit_status']['state'])) ?
                $attributes['commit_status']['state'] : null;

            $data['repository'] = (isset($attributes['commit_status']['repository']['full_name'])) ?
                $attributes['commit_status']['repository']['full_name'] : null;
        }

        return new self($data);
    }

    public function __get($name)
    {
        return $this->attributes[$name];
    }

    public function __set($name, $value)
    {
        return $this->attributes[$name] = $value;
    }
}
