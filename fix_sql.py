import sys

def test_decode():
    s = "Chß╗⌐ng chß╗ë H├án 6G"
    try:
        # It's usually: the original UTF-8 bytes were interpreted as CP850, then saved as UTF-8.
        # So we encode to CP850 to get the original UTF-8 bytes, then decode as UTF-8.
        b = s.encode('cp850')
        print("CP850 -> UTF-8:", b.decode('utf-8'))
    except Exception as e:
        print("CP850 Error:", e)

    try:
        b = s.encode('cp437')
        print("CP437 -> UTF-8:", b.decode('utf-8'))
    except Exception as e:
        print("CP437 Error:", e)

    try:
        b = s.encode('latin1')
        print("Latin1 -> UTF-8:", b.decode('utf-8'))
    except Exception as e:
        print("Latin1 Error:", e)

test_decode()
