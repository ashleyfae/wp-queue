<?php
namespace PHPSTORM_META {
    override(
        [\AshleyFae\WpQueue\WpQueue::class, 'get'],
        map( [
            '' => '@',
            '' => '@Class',
        ] )
    );
}
