<?php

/**
 *    Copyright 2015 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $table = 'osu_incidents';
    protected $primaryKey = 'incident_id';

    public $timestamps = false;
    public $dates = ['timestamp'];
    
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function hasAuthor()
    {
        return !is_null($this->author);
    }

    public function isParent()
    {
        return is_null($this->parent_id);
    }

    public function parent()
    {
        return $this->belongsTo(Incident::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Incident::class, 'parent_id', 'incident_id');
    }
}
