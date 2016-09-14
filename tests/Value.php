<?hh

namespace PhpLang\Phack\Test;

class Value<T> {
    private T $val;

    public function get(): T {
      return $this->val;
    }

    public function __construct(T $val) {
        $this->val = $val;
    }
}
