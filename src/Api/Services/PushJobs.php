<?php
namespace Kcloze\Jobs\Api\Services;

use Kcloze\Jobs\JobObject;
use Kcloze\Jobs\Logs;
use Kcloze\Jobs\Queue\BaseTopicQueue;
use Kcloze\Jobs\Queue\Queue;

class PushJobs
{
    /**
     * jobData is json string
     */
    public function pushSimple(string $jobData='')
    {
        if (!$jobData) {
            return \json_encode(['code'=>-3,'message'=>'sorry,jobData params is wrong.','content'=>$data]);
        }

        $data=\json_decode($jobData, true);
        $data['topic']=$data['topic']??'';
        $data['jobClass']=$data['jobClass']??'';
        $data['jobMethod']=$data['jobMethod']??'';
        $data['jobParams']=$data['jobParams']??'';
        $data['jobExtras']=$data['jobExtras']??'';
        $data['serializeFunc']=$data['serializeFunc']??'php';
        //检查参数是否有误
        if (!$data['topic'] || !$data['jobClass'] || !$data['jobClass'] || !$data['jobParams']) {
            return \json_encode(['code'=>-2,'message'=>'no,jobData params is wrong.','content'=>$data]);
        }
        $pushJobs=new PushJobs();
        $result=$pushJobs->push($data['topic'], $data['jobClass'], $data['jobMethod'], $data['jobParams'], $data['jobExtras'], $data['serializeFunc']);
        $data['uuid']=$result;
        if ($result) {
            return \json_encode(['code'=>100,'message'=>'ok,job has been pushed success.','content'=>$data]);
        } else {
            return \json_encode(['code'=>-1,'message'=>'sorry,job has been pushed fail.','content'=>$data]);
        }
    }
    public function push($topic, $jobClass, $jobMethod, $jobParams=[], $jobExtras=[], $serializeFunc='php')
    {
        $config        = require SWOOLE_JOBS_ROOT_PATH . '/config.php';
        $logger        = Logs::getLogger($config['logPath'] ?? '', $config['logSaveFileApp'] ?? '');
        $queue         =Queue::getQueue($config['job']['queue'], $logger);
        $queue->setTopics($config['job']['topics']);
        
        // $jobExtras['delay']    =$delay;
        // $jobExtras['priority'] =BaseTopicQueue::HIGH_LEVEL_1;
        $job           =new JobObject($topic, $jobClass, $jobMethod, $jobParams, $jobExtras);
        $result        =$queue->push($topic, $job, 1, $serializeFunc);
        return $result;
    }
}
