#include <iostream>
#include "config.h"

using namespace std;

int main(int argc,char *argv[])
{
	Config cfg(argv[1]);
	cfg.print();
	return 0;
}
