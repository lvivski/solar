<?php
class Memcachetag extends Memcachedi
{
	public function & get($id)
    {
		$value = parent::get($id);
		if ($value === FALSE) {
			return null;
		}

		if (! empty($value['tags']) && count($value['tags']) > 0) {
			$expired = false;

			foreach ($value['tags'] as $tag => $tag_value) {
				$tag_current_value = $this->_getTag($tag);
				if ($tag_current_value != $tag_value) {
					$expired = true;
					break;
				}
			}

			if ($expired) {
				return null;
			}
		}
		return $value['data'];
	}

   public function set($id, $data, array $tags = null, $lifetime)
   {
		if (! empty($tags)) {
			$key_tags = array();

			foreach ($tags as $tag) {
				$key_tags[$tag] = $this->_getTag($tag);
			}
			$key['tags'] = $key_tags;
		}

		$key['data'] = $data;
		if ($lifetime !== 0) {
			$lifetime += time();
		}

		parent::set($id, $key, $lifetime);

    }

	public function deleteTag($tag)
    {
		$key = 'tag_'.$tag;
		$tag_value = $this->_getTag($tag);

		$this->set($key, microtime(true), null, 60*60*24*30);
		return true;
	}

	private function & _getTag($tag)
    {
		$key = 'tag_'.$tag;
		$tag_value = $this->get($key);
		if ($tag_value === null) {
			$tag_value = microtime(true);
			$this->set($key, $tag_value, null,60*60*24*30);
		}
		return $tag_value;
	}
}
