#ifndef _TT_PROXY_GLOBAL_H_
#define _TT_PROXY_GLOBAL_H_
#include <iostream>
#include "queue.h"
#include "ttcircle.h"
#include <map>
using namespace std;

typedef SyncQueue<int>  FdQueue;

extern FdQueue g_fdqueue; 
extern map<string ,TTCircle*> g_ttc;

extern string g_cfg;
void stringReplace(string& name,string src,string dst);
void * work_thread(void * p);
void * udp_thread(void * p);
#endif
