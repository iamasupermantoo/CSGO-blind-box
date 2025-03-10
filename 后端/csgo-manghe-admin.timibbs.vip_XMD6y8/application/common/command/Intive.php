<?php
namespace app\common\command;
use app\common\controller\Recommend;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * Class Intive
 *
 * @package app\common\command
 */
class Intive extends Command
{
    //重置游戏
    protected function configure()
    {
        $this->setName('invite')
            // 配置一个参数
            ->addArgument('player_id', Argument::OPTIONAL, '用户ID')
            ->setDescription('邀请计算收益');
    }

    protected function execute(Input $input, Output $output)
    {
        $player_id = $input->getArgument('player_id');

        $output->writeln("======Start======");
        Recommend::run($player_id);

    }
}