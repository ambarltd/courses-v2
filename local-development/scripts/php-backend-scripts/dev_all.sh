#!/bin/bash
set -e

./dev_depen_check.sh
./dev_linter_check.sh
./dev_test.sh
./dev_type_check.sh
