from sys import argv
from os import getenv, path, makedirs
from re import sub

SUBSTITUTION_FROM_HOST = 0
SUBSTITUTION_DEFAULT = 1
SUBSTITUTION_NONE = 2

def parseArg(argument: str) -> tuple[str, str, int]:
    """
    For an argument of format "HOST_VAR?default_value", check for HOST_VAR in the
    host's environment variables, and if that variable isn't defined, default to
    default_value. If default_value is not present, return a blank string.
    """
    split_argument = argument.split('?', 1)
    host_value = getenv(split_argument[0])
    default_value = "" if len(split_argument) == 1 else split_argument[1]
    return (
        split_argument[0],
        host_value or default_value,
        SUBSTITUTION_FROM_HOST if host_value else SUBSTITUTION_DEFAULT if len(default_value) > 0 else SUBSTITUTION_NONE
    )

def main():
    if len(argv) < 4:
        print("usage: <source file> <dest file> ...<var[?default>]")
        exit()

    # Grab relevant vars
    source_path = argv[1]
    dest_path = argv[2]
    substitutions = [parseArg(x) for x in argv[3:]]

    # Ensure source path exists
    assert path.exists(source_path), "No such file: " + source_path

    # Ensure dest directory exists
    dest_directory = "/".join(dest_path.split("/")[:-1])
    makedirs(dest_directory, exist_ok=True)

    with open(source_path, 'r') as source_file, open(dest_path, 'w') as dest_file:
        full_source = source_file.read()
        for (variable_name, substitution, sub_type) in substitutions:
            # Default syntax: {{ HOST_VAR }}
            # Whitespace is optional.
            full_source = sub(r"\$\{\{\s?" + variable_name + r"\s?\}\}", substitution, full_source)

            # Only if defined syntax: {{= HOST_VAR }}
            # Will only populate a value if the captured variable has the SUBSTITUTION_FROM_HOST flag.
            full_source = sub(
                r"\$\{\{=\s?" + variable_name + r"\s?\}\}",
                substitution if sub_type == SUBSTITUTION_FROM_HOST else '',
                full_source
            )
        dest_file.write(full_source)

if __name__ == "__main__":
    main()